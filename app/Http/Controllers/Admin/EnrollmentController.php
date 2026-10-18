<?php

namespace App\Http\Controllers\Admin;

use App\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EnrollmentRequest;
use App\Http\Requests\Admin\EnrollmentStatusRequest;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    /**
     * Jumlah pendaftaran per halaman.
     */
    private const PER_PAGE = 10;

    /**
     * Menampilkan daftar pendaftaran dengan pencarian, filter, dan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'program' => ['nullable', 'integer', Rule::exists('programs', 'id')],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')],
            'status' => ['nullable', Rule::enum(EnrollmentStatus::class)],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
            'riwayat' => ['nullable', 'boolean'],
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir filter tidak boleh lebih awal daripada tanggal awal.',
        ]);

        $isHistory = (bool) ($filters['riwayat'] ?? false);
        $search = trim($filters['q'] ?? '');
        $programId = isset($filters['program']) ? (int) $filters['program'] : null;
        $classroomId = isset($filters['kelas']) ? (int) $filters['kelas'] : null;
        $status = isset($filters['status']) ? EnrollmentStatus::from($filters['status']) : null;
        $dateFrom = $filters['dari'] ?? null;
        $dateTo = $filters['sampai'] ?? null;

        $enrollments = Enrollment::query()
            ->with(['student', 'program', 'classroom'])
            ->when($search !== '', fn (Builder $query) => $query->whereHas('student', function (Builder $query) use ($search): void {
                $query->withTrashed()->where(fn (Builder $query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            }))
            ->when($programId, fn (Builder $query, int $id) => $query->where('program_id', $id))
            ->when($classroomId, fn (Builder $query, int $id) => $query->where('classroom_id', $id))
            ->when($status, fn (Builder $query, EnrollmentStatus $status) => $query->where('status', $status->value))
            ->when($isHistory, fn (Builder $query) => $query->whereIn('status', [EnrollmentStatus::Rejected->value, EnrollmentStatus::Cancelled->value]))
            ->when($dateFrom, fn (Builder $query, string $date) => $query->where('enrolled_on', '>=', $date))
            ->when($dateTo, fn (Builder $query, string $date) => $query->where('enrolled_on', '<=', $date))
            ->orderByDesc('enrolled_on')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.enrollments.index', [
            'enrollments' => $enrollments,
            'search' => $search,
            'programId' => $programId,
            'classroomId' => $classroomId,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'isHistory' => $isHistory,
            'statuses' => EnrollmentStatus::cases(),
            'programs' => Program::query()->orderBy('name')->get(['id', 'name']),
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    /**
     * Menampilkan form tambah pendaftaran.
     */
    public function create(Request $request): View
    {
        return view('admin.enrollments.create', [
            ...$this->formOptions(null),
            'preselectedStudentId' => $request->integer('murid') ?: null,
        ]);
    }

    /**
     * Menyimpan pendaftaran baru setelah memeriksa pendaftaran ganda dan kapasitas kelas di dalam transaksi.
     */
    public function store(EnrollmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $enrollment = DB::transaction(function () use ($validated): Enrollment {
            Enrollment::ensureCanEnroll(
                (int) $validated['student_id'],
                (int) $validated['program_id'],
                isset($validated['classroom_id']) ? (int) $validated['classroom_id'] : null,
            );

            $enrollment = new Enrollment($validated);
            $enrollment->student_id = (int) $validated['student_id'];
            $enrollment->status = EnrollmentStatus::Pending;
            $enrollment->bill_amount = Program::query()->whereKey($validated['program_id'])->value('price');
            $enrollment->save();

            return $enrollment;
        });

        $enrollment->load(['student', 'program']);

        return redirect()
            ->route('admin.enrollments.index')
            ->with('success', "Pendaftaran {$enrollment->student->name} ke program {$enrollment->program->name} berhasil dibuat dengan status Menunggu Verifikasi.");
    }

    /**
     * Menampilkan detail pendaftaran.
     */
    public function show(Enrollment $enrollment): View
    {
        $enrollment->load([
            'student.studentProfile',
            'program',
            'classroom.teacher',
            'payments' => fn ($query) => $query->latest('paid_on')->latest('id'),
        ]);

        return view('admin.enrollments.show', [
            'enrollment' => $enrollment,
            'classroomOccupancy' => $enrollment->classroom === null ? null : $this->occupiedSeats($enrollment->classroom),
        ]);
    }

    /**
     * Menampilkan form edit pendaftaran sesuai field yang masih boleh diubah.
     */
    public function edit(Enrollment $enrollment): View
    {
        $enrollment->load(['student', 'program', 'classroom']);

        return view('admin.enrollments.edit', [
            'enrollment' => $enrollment,
            ...$this->formOptions($enrollment),
            'preselectedStudentId' => null,
        ]);
    }

    /**
     * Memperbarui pendaftaran. Penggantian program/kelas diperiksa ulang terhadap pendaftaran ganda dan kapasitas.
     */
    public function update(EnrollmentRequest $request, Enrollment $enrollment): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $enrollment): void {
            $programId = (int) ($validated['program_id'] ?? $enrollment->program_id);
            $classroomId = array_key_exists('classroom_id', $validated)
                ? ($validated['classroom_id'] === null ? null : (int) $validated['classroom_id'])
                : $enrollment->classroom_id;

            $isProgramChanged = $programId !== $enrollment->program_id;
            $isClassroomChanged = $classroomId !== $enrollment->classroom_id;

            if ($enrollment->status->isActive() && ($isProgramChanged || $isClassroomChanged)) {
                Enrollment::ensureCanEnroll(
                    $enrollment->student_id,
                    $programId,
                    $isClassroomChanged ? $classroomId : null,
                    $enrollment->id,
                );
            }

            if ($isProgramChanged) {
                $validated['bill_amount'] = Program::query()->whereKey($programId)->value('price');
            }

            $enrollment->fill($validated)->save();
        });

        return redirect()
            ->route('admin.enrollments.show', $enrollment)
            ->with('success', 'Data pendaftaran berhasil diperbarui.');
    }

    /**
     * Mengubah status pendaftaran sesuai alur yang diizinkan. Data pendaftaran tidak pernah dihapus.
     */
    public function updateStatus(EnrollmentStatusRequest $request, Enrollment $enrollment): RedirectResponse
    {
        $target = EnrollmentStatus::from($request->validated('status'));

        DB::transaction(fn () => $enrollment->transitionTo($target));

        $enrollment->load('student');

        return back()->with('success', "Status pendaftaran {$enrollment->student->name} berhasil diubah menjadi {$target->label()}.");
    }

    /**
     * Pilihan murid, program, dan kelas untuk form pendaftaran.
     *
     * Hanya murid yang belum dihapus, program aktif, dan kelas aktif yang ditampilkan, ditambah
     * program/kelas yang sedang terpasang pada pendaftaran yang diedit.
     *
     * @return array{students: Collection<int, User>, programs: Collection<int, Program>, classrooms: Collection<int, Classroom>}
     */
    private function formOptions(?Enrollment $enrollment): array
    {
        $students = User::query()->students()->orderBy('name')->get(['id', 'name', 'email']);

        $programs = Program::query()
            ->where(fn (Builder $query) => $query->where('is_active', true)
                ->when($enrollment, fn (Builder $query) => $query->orWhere('id', $enrollment->program_id)))
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);

        $classrooms = Classroom::query()
            ->where(fn (Builder $query) => $query->where('is_active', true)
                ->when($enrollment?->classroom_id, fn (Builder $query, int $id) => $query->orWhere('id', $id)))
            ->withCount(['enrollments as occupied_seats' => fn (Builder $query) => $query->active()])
            ->orderBy('name')
            ->get(['id', 'program_id', 'name', 'code', 'capacity', 'is_active']);

        return [
            'students' => $students,
            'programs' => $programs,
            'classrooms' => $classrooms,
        ];
    }

    /**
     * Jumlah murid berbeda yang menempati kursi kelas (pendaftaran aktif).
     */
    private function occupiedSeats(Classroom $classroom): int
    {
        return $classroom->enrollments()->active()->distinct()->count('student_id');
    }
}
