<?php

namespace App\Http\Controllers\Admin;

use App\AttendanceStatus;
use App\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CorrectAttendanceRequest;
use App\Http\Requests\Admin\StoreStudentAttendanceRequest;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Weekday;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentAttendanceController extends Controller
{
    /**
     * Jumlah baris per halaman.
     */
    private const PER_PAGE = 15;

    /**
     * Riwayat dan rekap absensi murid dengan pencarian, filter, dan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $viewMode = $filters['tampilan'] ?? 'riwayat';
        $groupBy = $filters['kelompok'] ?? 'murid';

        $statusCounts = $this->filteredQuery($filters, includeStatus: false)
            ->selectRaw('student_attendances.status as status, count(*) as total')
            ->groupBy('student_attendances.status')
            ->pluck('total', 'status');

        $records = null;
        $recap = null;

        if ($viewMode === 'rekap') {
            $recap = $this->recap($filters, $groupBy);
        } else {
            $records = $this->filteredQuery($filters, includeStatus: true)
                ->with(['student', 'schedule.classroom.teacher'])
                ->orderByDesc('student_attendances.session_date')
                ->orderByDesc('student_attendances.id')
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        }

        return view('admin.attendances.students.index', [
            'records' => $records,
            'recap' => $recap,
            'viewMode' => $viewMode,
            'groupBy' => $groupBy,
            'filters' => $filters,
            'statusCounts' => collect(AttendanceStatus::cases())->mapWithKeys(fn (AttendanceStatus $status) => [$status->value => (int) ($statusCounts[$status->value] ?? 0)]),
            'statuses' => AttendanceStatus::cases(),
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name']),
            'teachers' => User::query()->teachers()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Form pencatatan absensi: pilih tanggal, lalu jadwal yang berlangsung, lalu isi status tiap murid.
     */
    public function create(Request $request): View
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'jadwal' => ['nullable', 'integer', Rule::exists('schedules', 'id')],
        ], [
            'tanggal.before_or_equal' => 'Absensi tidak dapat dicatat untuk tanggal yang akan datang.',
        ]);

        $date = CarbonImmutable::parse($validated['tanggal'] ?? now()->format('Y-m-d'));
        $schedules = $this->schedulesOn($date);
        $selectedSchedule = isset($validated['jadwal']) ? $schedules->firstWhere('id', (int) $validated['jadwal']) : null;

        $roster = collect();
        $existingRecords = collect();
        $hasStarted = false;

        if ($selectedSchedule !== null) {
            $roster = $this->roster($selectedSchedule, $date);
            $existingRecords = StudentAttendance::query()
                ->where('schedule_id', $selectedSchedule->id)
                ->where('session_date', $date->format('Y-m-d'))
                ->get()
                ->keyBy('student_id');
            $hasStarted = now()->greaterThanOrEqualTo($selectedSchedule->sessionStartsAt($date));
        }

        return view('admin.attendances.students.create', [
            'date' => $date,
            'schedules' => $schedules,
            'selectedSchedule' => $selectedSchedule,
            'invalidScheduleSelected' => isset($validated['jadwal']) && $selectedSchedule === null,
            'roster' => $roster,
            'existingRecords' => $existingRecords,
            'hasStarted' => $hasStarted,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    /**
     * Menyimpan absensi untuk murid peserta sesi yang belum memiliki catatan.
     *
     * Catatan yang sudah ada tidak diubah (gunakan koreksi), sehingga pengiriman ulang formulir
     * tidak membuat atau menimpa data. Constraint unik di database menjadi pengaman terakhir.
     */
    public function store(StoreStudentAttendanceRequest $request): RedirectResponse
    {
        $schedule = Schedule::with('classroom')->findOrFail($request->integer('schedule_id'));
        $date = CarbonImmutable::parse((string) $request->input('session_date'));
        /** @var array<int|string, array{status: string, notes?: string|null}> $submitted */
        $submitted = $request->validated('attendances');

        $rosterByEnrollment = $this->roster($schedule, $date)->keyBy('id');
        $unknownEnrollmentIds = array_diff(array_map('intval', array_keys($submitted)), $rosterByEnrollment->keys()->all());

        if ($unknownEnrollmentIds !== []) {
            throw ValidationException::withMessages([
                'attendances' => 'Terdapat murid yang bukan peserta aktif kelas ini pada tanggal tersebut.',
            ]);
        }

        [$createdCount, $skippedCount] = DB::transaction(function () use ($schedule, $date, $submitted, $rosterByEnrollment, $request): array {
            Schedule::query()->whereKey($schedule->id)->lockForUpdate()->first();

            $alreadyRecordedStudentIds = StudentAttendance::query()
                ->where('schedule_id', $schedule->id)
                ->where('session_date', $date->format('Y-m-d'))
                ->pluck('student_id')
                ->all();

            $created = 0;
            $skipped = 0;

            foreach ($submitted as $enrollmentId => $entry) {
                /** @var Enrollment $enrollment */
                $enrollment = $rosterByEnrollment->get((int) $enrollmentId);

                if (in_array($enrollment->student_id, $alreadyRecordedStudentIds, true)) {
                    $skipped++;

                    continue;
                }

                $attendance = new StudentAttendance([
                    'status' => $entry['status'],
                    'notes' => filled($entry['notes'] ?? null) ? trim($entry['notes']) : null,
                ]);
                $attendance->schedule_id = $schedule->id;
                $attendance->session_date = $date;
                $attendance->student_id = $enrollment->student_id;
                $attendance->enrollment_id = $enrollment->id;
                $attendance->recorded_by = $request->user()->id;
                $attendance->save();

                $alreadyRecordedStudentIds[] = $enrollment->student_id;
                $created++;
            }

            return [$created, $skipped];
        });

        $message = "Absensi {$createdCount} murid untuk {$schedule->classroom->name} pada {$date->translatedFormat('d F Y')} berhasil disimpan.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} murid dilewati karena sudah tercatat (gunakan koreksi untuk mengubah).";
        }

        return redirect()
            ->route('admin.attendances.students.create', ['tanggal' => $date->format('Y-m-d'), 'jadwal' => $schedule->id])
            ->with('success', $message);
    }

    /**
     * Form koreksi status absensi murid.
     */
    public function edit(StudentAttendance $studentAttendance): View
    {
        $studentAttendance->load(['student', 'schedule.classroom.teacher', 'recorder', 'corrector']);

        return view('admin.attendances.students.edit', [
            'attendance' => $studentAttendance,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    /**
     * Menyimpan koreksi status absensi murid beserta alasan dan status sebelumnya.
     */
    public function update(CorrectAttendanceRequest $request, StudentAttendance $studentAttendance): RedirectResponse
    {
        DB::transaction(fn () => $studentAttendance->correctStatus(
            AttendanceStatus::from($request->validated('status')),
            $request->validated('reason'),
            $request->user(),
        ));

        return redirect()
            ->route('admin.attendances.students.index')
            ->with('success', "Absensi {$studentAttendance->student->name} pada {$studentAttendance->session_date->translatedFormat('d F Y')} berhasil dikoreksi menjadi {$studentAttendance->status->label()}.");
    }

    /**
     * Validasi parameter filter daftar dan rekap.
     *
     * @return array<string, mixed>
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')],
            'guru' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'status' => ['nullable', Rule::enum(AttendanceStatus::class)],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
            'tampilan' => ['nullable', Rule::in(['riwayat', 'rekap'])],
            'kelompok' => ['nullable', Rule::in(['murid', 'kelas'])],
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir periode tidak boleh lebih awal daripada tanggal awal.',
        ]);
    }

    /**
     * Query absensi yang sudah difilter. Kolom ditulis lengkap dengan nama tabel agar aman saat di-join.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<StudentAttendance>
     */
    private function filteredQuery(array $filters, bool $includeStatus): Builder
    {
        $search = trim($filters['q'] ?? '');

        return StudentAttendance::query()
            ->when($search !== '', fn (Builder $query) => $query->whereHas('student', fn (Builder $query) => $query->withTrashed()->where(
                fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")
            )))
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->whereHas('schedule', fn (Builder $query) => $query->where('classroom_id', (int) $id)))
            ->when($filters['guru'] ?? null, fn (Builder $query, $id) => $query->whereHas('schedule.classroom', fn (Builder $query) => $query->where('teacher_id', (int) $id)))
            ->when($includeStatus && isset($filters['status']), fn (Builder $query) => $query->where('student_attendances.status', $filters['status']))
            ->when($filters['dari'] ?? null, fn (Builder $query, string $date) => $query->where('student_attendances.session_date', '>=', $date))
            ->when($filters['sampai'] ?? null, fn (Builder $query, string $date) => $query->where('student_attendances.session_date', '<=', $date));
    }

    /**
     * Rekap jumlah Hadir/Izin/Sakit/Alpa per murid atau per kelas.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator
     */
    private function recap(array $filters, string $groupBy)
    {
        $statusSums = collect(AttendanceStatus::cases())
            ->map(fn (AttendanceStatus $status) => "sum(case when student_attendances.status = '{$status->value}' then 1 else 0 end) as {$status->value}_count")
            ->implode(', ');

        if ($groupBy === 'kelas') {
            $rows = $this->filteredQuery($filters, includeStatus: false)
                ->join('schedules', 'schedules.id', '=', 'student_attendances.schedule_id')
                ->selectRaw("schedules.classroom_id as group_id, count(*) as total_count, {$statusSums}")
                ->groupBy('schedules.classroom_id')
                ->orderBy('schedules.classroom_id')
                ->paginate(self::PER_PAGE)
                ->withQueryString();

            $names = Classroom::query()->whereIn('id', $rows->pluck('group_id'))->pluck('name', 'id');
        } else {
            $rows = $this->filteredQuery($filters, includeStatus: false)
                ->selectRaw("student_attendances.student_id as group_id, count(*) as total_count, {$statusSums}")
                ->groupBy('student_attendances.student_id')
                ->orderBy('student_attendances.student_id')
                ->paginate(self::PER_PAGE)
                ->withQueryString();

            $names = User::withTrashed()->whereIn('id', $rows->pluck('group_id'))->pluck('name', 'id');
        }

        $rows->getCollection()->transform(function ($row) use ($names) {
            $row->group_name = $names[$row->group_id] ?? '—';

            return $row;
        });

        return $rows;
    }

    /**
     * Jadwal aktif (kelas aktif) yang berlangsung pada tanggal tertentu.
     *
     * @return Collection<int, Schedule>
     */
    private function schedulesOn(CarbonImmutable $date): Collection
    {
        return Schedule::query()
            ->where('is_active', true)
            ->where('day_of_week', Weekday::fromDate($date)->value)
            ->whereHas('classroom', fn (Builder $query) => $query->where('is_active', true))
            ->with(['classroom.teacher', 'classroom.program'])
            ->orderBy('start_time')
            ->get()
            ->filter(fn (Schedule $schedule) => $schedule->occursOn($date))
            ->values();
    }

    /**
     * Peserta sesi: murid dengan pendaftaran Diterima di kelas jadwal, terdaftar sebelum atau pada tanggal sesi.
     *
     * @return Collection<int, Enrollment>
     */
    private function roster(Schedule $schedule, CarbonImmutable $date): Collection
    {
        return Enrollment::query()
            ->where('classroom_id', $schedule->classroom_id)
            ->where('status', EnrollmentStatus::Approved->value)
            ->where('enrolled_on', '<=', $date->format('Y-m-d'))
            ->with('student')
            ->get()
            ->sortBy(fn (Enrollment $enrollment) => $enrollment->student->name)
            ->values();
    }
}
