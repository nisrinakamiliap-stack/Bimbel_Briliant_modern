<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClassroomRequest;
use App\LearningMode;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassroomController extends Controller
{
    /**
     * Jumlah kelas per halaman.
     */
    private const PER_PAGE = 10;

    /**
     * Menampilkan daftar kelas dengan pencarian, filter program/status, dan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'program' => ['nullable', 'integer', Rule::exists('programs', 'id')],
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
        ]);

        $search = trim($filters['q'] ?? '');
        $programId = isset($filters['program']) ? (int) $filters['program'] : null;
        $status = $filters['status'] ?? null;

        $classrooms = Classroom::query()
            ->with(['program', 'teacher'])
            ->withCount(['enrollments as occupied_seats' => fn (Builder $query) => $query->active()])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($programId, fn (Builder $query, int $programId) => $query->where('program_id', $programId))
            ->when($status, fn (Builder $query, string $status) => $query->where('is_active', $status === 'aktif'))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.classes.index', [
            'classrooms' => $classrooms,
            'search' => $search,
            'programId' => $programId,
            'status' => $status,
            'programs' => Program::query()->orderBy('name')->get(['id', 'name', 'is_active']),
        ]);
    }

    /**
     * Menampilkan form tambah kelas.
     */
    public function create(): View
    {
        return view('admin.classes.create', $this->formOptions(null));
    }

    /**
     * Menyimpan kelas baru lalu kembali ke daftar kelas.
     */
    public function store(ClassroomRequest $request): RedirectResponse
    {
        $classroom = Classroom::create($request->validated());

        return redirect()
            ->route('admin.classes.index')
            ->with('success', "Kelas {$classroom->name} berhasil ditambahkan.");
    }

    /**
     * Menampilkan detail kelas.
     */
    public function show(Classroom $classroom): View
    {
        $classroom->load([
            'program',
            'teacher.teacherProfile',
            'schedules' => fn ($query) => $query->orderBy('day_of_week')->orderBy('start_time'),
            'enrollments' => fn ($query) => $query->active()->with('student')->orderBy('enrolled_on'),
        ]);

        return view('admin.classes.show', [
            'classroom' => $classroom,
            'isDeletable' => ! $classroom->hasDependentRecords(),
        ]);
    }

    /**
     * Menampilkan form edit kelas.
     */
    public function edit(Classroom $classroom): View
    {
        return view('admin.classes.edit', [
            'classroom' => $classroom,
            ...$this->formOptions($classroom),
        ]);
    }

    /**
     * Memperbarui data kelas.
     */
    public function update(ClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $classroom->update($request->validated());

        return redirect()
            ->route('admin.classes.show', $classroom)
            ->with('success', "Kelas {$classroom->name} berhasil diperbarui.");
    }

    /**
     * Mengaktifkan atau menonaktifkan kelas.
     */
    public function toggleStatus(Classroom $classroom): RedirectResponse
    {
        $classroom->is_active = ! $classroom->is_active;
        $classroom->save();

        $message = $classroom->is_active
            ? "Kelas {$classroom->name} berhasil diaktifkan."
            : "Kelas {$classroom->name} berhasil dinonaktifkan.";

        return back()->with('success', $message);
    }

    /**
     * Menghapus kelas secara permanen hanya jika belum memiliki jadwal, pendaftaran, atau absensi.
     *
     * Kelas yang sudah memiliki riwayat tidak dihapus; admin diarahkan untuk menonaktifkannya.
     */
    public function destroy(Classroom $classroom): RedirectResponse
    {
        if ($classroom->hasDependentRecords()) {
            return back()->with(
                'error',
                "Kelas {$classroom->name} tidak dapat dihapus karena sudah memiliki jadwal, pendaftaran, atau absensi. Nonaktifkan kelas ini agar riwayatnya tetap tersimpan."
            );
        }

        $classroom->delete();

        return redirect()
            ->route('admin.classes.index')
            ->with('success', "Kelas {$classroom->name} berhasil dihapus.");
    }

    /**
     * Pilihan program, guru, dan mode belajar untuk form kelas.
     *
     * Hanya program aktif dan guru yang belum dihapus yang dapat dipilih, ditambah program/guru
     * yang sedang terpasang pada kelas yang diedit agar tetap tampil di form.
     *
     * @return array{programs: Collection<int, Program>, teachers: Collection<int, User>, learningModes: array<int, LearningMode>}
     */
    private function formOptions(?Classroom $classroom): array
    {
        $programs = Program::query()
            ->where(function (Builder $query) use ($classroom): void {
                $query->where('is_active', true);
                if ($classroom !== null) {
                    $query->orWhere('id', $classroom->program_id);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);

        $teachers = User::query()
            ->teachers()
            ->when($classroom?->teacher_id, fn (Builder $query, int $teacherId) => $query->withTrashed()->where(
                fn (Builder $query) => $query->whereNull('deleted_at')->orWhere('id', $teacherId)
            ))
            ->with('teacherProfile')
            ->orderBy('name')
            ->get();

        return [
            'programs' => $programs,
            'teachers' => $teachers,
            'learningModes' => LearningMode::cases(),
        ];
    }
}
