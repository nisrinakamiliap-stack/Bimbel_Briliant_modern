<?php

namespace App\Http\Controllers\Teacher;

use App\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentAttendanceRequest;
use App\Models\Schedule;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Weekday;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Absensi murid pada kelas yang diampu guru: riwayat, ringkasan, dan pencatatan per sesi.
 *
 * Koreksi catatan yang sudah tersimpan tetap dilakukan admin agar riwayat perubahan terjaga.
 */
class StudentAttendanceController extends Controller
{
    /**
     * Jumlah baris riwayat per halaman.
     */
    private const PER_PAGE = 15;

    /**
     * Riwayat dan ringkasan absensi murid kelas yang diampu.
     */
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $filters = $request->validate([
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')->where('teacher_id', $teacher->id)],
            'status' => ['nullable', Rule::enum(AttendanceStatus::class)],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal daripada tanggal awal.',
        ]);

        $query = fn (): Builder => StudentAttendance::query()
            ->whereHas('schedule.classroom', fn (Builder $query) => $query->where('teacher_id', $teacher->id))
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->whereHas('schedule', fn (Builder $query) => $query->where('classroom_id', (int) $id)))
            ->when($filters['dari'] ?? null, fn (Builder $query, string $date) => $query->where('session_date', '>=', $date))
            ->when($filters['sampai'] ?? null, fn (Builder $query, string $date) => $query->where('session_date', '<=', $date));

        $statusCounts = $query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->toBase()
            ->pluck('total', 'status');

        return view('teacher.attendances.index', [
            'records' => $query()
                ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
                ->with(['student', 'schedule.classroom'])
                ->orderByDesc('session_date')
                ->orderByDesc('id')
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
            'statusCounts' => collect(AttendanceStatus::cases())->mapWithKeys(fn (AttendanceStatus $status) => [$status->value => (int) ($statusCounts[$status->value] ?? 0)]),
            'filters' => $filters,
            'statuses' => AttendanceStatus::cases(),
            'classrooms' => $teacher->taughtClasses()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Form pencatatan: pilih tanggal, lalu sesi milik guru pada tanggal itu, lalu status tiap murid.
     */
    public function create(Request $request): View
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'jadwal' => ['nullable', 'integer'],
        ], [
            'tanggal.before_or_equal' => 'Absensi tidak dapat dicatat untuk tanggal yang akan datang.',
        ]);

        $date = CarbonImmutable::parse($validated['tanggal'] ?? CarbonImmutable::now()->format('Y-m-d'))->startOfDay();
        $schedules = $this->schedulesOn($request->user(), $date);
        $selectedSchedule = isset($validated['jadwal']) ? $schedules->firstWhere('id', (int) $validated['jadwal']) : null;

        $roster = collect();
        $existingRecords = collect();
        $hasStarted = false;

        if ($selectedSchedule !== null) {
            $roster = StudentAttendance::rosterFor($selectedSchedule, $date);
            $existingRecords = StudentAttendance::query()
                ->where('schedule_id', $selectedSchedule->id)
                ->where('session_date', $date->format('Y-m-d'))
                ->get()
                ->keyBy('student_id');
            $hasStarted = CarbonImmutable::now()->greaterThanOrEqualTo($selectedSchedule->sessionStartsAt($date));
        }

        return view('teacher.attendances.create', [
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
     * Menyimpan absensi sesi. {@see StoreStudentAttendanceRequest} memastikan jadwal milik kelas guru ini.
     */
    public function store(StoreStudentAttendanceRequest $request): RedirectResponse
    {
        $schedule = Schedule::with('classroom')->findOrFail($request->integer('schedule_id'));
        $date = CarbonImmutable::parse((string) $request->input('session_date'));

        [$createdCount, $skippedCount] = StudentAttendance::recordSession($schedule, $date, $request->validated('attendances'), $request->user());

        $message = "Absensi {$createdCount} murid untuk {$schedule->classroom->name} pada {$date->translatedFormat('d F Y')} berhasil disimpan.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} murid dilewati karena sudah tercatat. Hubungi admin untuk koreksi.";
        }

        return redirect()
            ->route('teacher.attendances.create', ['tanggal' => $date->format('Y-m-d'), 'jadwal' => $schedule->id])
            ->with('success', $message);
    }

    /**
     * Jadwal aktif milik kelas aktif yang diampu guru dan berlangsung pada tanggal tertentu.
     *
     * @return Collection<int, Schedule>
     */
    private function schedulesOn(User $teacher, CarbonImmutable $date): Collection
    {
        return Schedule::query()
            ->where('is_active', true)
            ->where('day_of_week', Weekday::fromDate($date)->value)
            ->whereHas('classroom', fn (Builder $query) => $query->where('teacher_id', $teacher->id)->where('is_active', true))
            ->with('classroom.program')
            ->orderBy('start_time')
            ->get()
            ->filter(fn (Schedule $schedule) => $schedule->occursOn($date))
            ->values();
    }
}
