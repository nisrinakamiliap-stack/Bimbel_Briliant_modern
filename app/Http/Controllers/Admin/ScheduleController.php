<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\User;
use App\Weekday;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    /**
     * Jumlah jadwal per halaman pada tampilan tabel.
     */
    private const PER_PAGE = 10;

    /**
     * Menampilkan jadwal dalam bentuk tabel (dengan pagination) atau kalender mingguan.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')],
            'guru' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'hari' => ['nullable', Rule::enum(Weekday::class)],
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            'tampilan' => ['nullable', Rule::in(['tabel', 'kalender'])],
        ]);

        $search = trim($filters['q'] ?? '');
        $classroomId = isset($filters['kelas']) ? (int) $filters['kelas'] : null;
        $teacherId = isset($filters['guru']) ? (int) $filters['guru'] : null;
        $day = isset($filters['hari']) ? Weekday::from((int) $filters['hari']) : null;
        $status = $filters['status'] ?? null;
        $viewMode = $filters['tampilan'] ?? 'tabel';

        $query = Schedule::query()
            ->with(['classroom.program', 'classroom.teacher'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('classroom', function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('teacher', fn (Builder $query) => $query->withTrashed()->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($classroomId, fn (Builder $query, int $id) => $query->where('classroom_id', $id))
            ->when($teacherId, fn (Builder $query, int $id) => $query->whereHas('classroom', fn (Builder $query) => $query->where('teacher_id', $id)))
            ->when($day, fn (Builder $query, Weekday $day) => $query->where('day_of_week', $day->value))
            ->when($status, fn (Builder $query, string $status) => $query->where('is_active', $status === 'aktif'))
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        $schedules = $viewMode === 'kalender'
            ? $query->get()
            : $query->paginate(self::PER_PAGE)->withQueryString();

        return view('admin.schedules.index', [
            'schedules' => $schedules,
            'viewMode' => $viewMode,
            'search' => $search,
            'classroomId' => $classroomId,
            'teacherId' => $teacherId,
            'day' => $day,
            'status' => $status,
            'weekdays' => Weekday::cases(),
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name', 'code']),
            'teachers' => User::query()->teachers()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Menampilkan form tambah jadwal.
     */
    public function create(Request $request): View
    {
        $preselectedClassroomId = $request->integer('kelas') ?: null;

        return view('admin.schedules.create', [
            ...$this->formOptions(null),
            'preselectedClassroomId' => $preselectedClassroomId,
        ]);
    }

    /**
     * Menyimpan jadwal baru lalu kembali ke daftar jadwal.
     */
    public function store(ScheduleRequest $request): RedirectResponse
    {
        $schedule = Schedule::create($request->validated());
        $schedule->load('classroom');

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', "Jadwal {$schedule->classroom->name} pada {$schedule->summary()} berhasil ditambahkan.");
    }

    /**
     * Menampilkan detail jadwal.
     */
    public function show(Schedule $schedule): View
    {
        $schedule->load(['classroom.program', 'classroom.teacher.teacherProfile']);

        return view('admin.schedules.show', [
            'schedule' => $schedule,
            'isDeletable' => ! $schedule->hasDependentRecords(),
        ]);
    }

    /**
     * Menampilkan form edit jadwal.
     */
    public function edit(Schedule $schedule): View
    {
        return view('admin.schedules.edit', [
            'schedule' => $schedule,
            ...$this->formOptions($schedule),
            'preselectedClassroomId' => null,
        ]);
    }

    /**
     * Memperbarui jadwal.
     */
    public function update(ScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        $schedule->update($request->validated());

        return redirect()
            ->route('admin.schedules.show', $schedule)
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    /**
     * Mengaktifkan atau menonaktifkan jadwal.
     *
     * Saat diaktifkan kembali, jadwal diperiksa ulang agar tidak bentrok dengan jadwal aktif lain.
     */
    public function toggleStatus(Schedule $schedule): RedirectResponse
    {
        $schedule->load('classroom');

        if (! $schedule->is_active) {
            $conflict = Schedule::findConflict(
                $schedule->classroom,
                $schedule->day_of_week,
                $schedule->start_time,
                $schedule->end_time,
                $schedule->starts_on?->format('Y-m-d'),
                $schedule->ends_on?->format('Y-m-d'),
                $schedule->id,
            );

            if ($conflict !== null) {
                return back()->with('error', 'Jadwal tidak dapat diaktifkan. '.Schedule::conflictMessage($conflict));
            }
        }

        $schedule->is_active = ! $schedule->is_active;
        $schedule->save();

        $message = $schedule->is_active
            ? "Jadwal {$schedule->classroom->name} pada {$schedule->summary()} berhasil diaktifkan."
            : "Jadwal {$schedule->classroom->name} pada {$schedule->summary()} berhasil dinonaktifkan.";

        return back()->with('success', $message);
    }

    /**
     * Menghapus jadwal secara permanen hanya jika belum memiliki riwayat (mis. absensi).
     */
    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->load('classroom');

        if ($schedule->hasDependentRecords()) {
            return back()->with(
                'error',
                'Jadwal ini tidak dapat dihapus karena sudah memiliki riwayat absensi atau pembelajaran. Nonaktifkan jadwal ini agar riwayatnya tetap tersimpan.'
            );
        }

        $schedule->delete();

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', "Jadwal {$schedule->classroom->name} pada {$schedule->summary()} berhasil dihapus.");
    }

    /**
     * Pilihan kelas dan hari untuk form jadwal.
     *
     * Hanya kelas aktif yang dapat dipilih, ditambah kelas yang sedang terpasang pada jadwal yang diedit.
     *
     * @return array{classrooms: Collection<int, Classroom>, weekdays: array<int, Weekday>}
     */
    private function formOptions(?Schedule $schedule): array
    {
        $classrooms = Classroom::query()
            ->with(['program', 'teacher'])
            ->where(function (Builder $query) use ($schedule): void {
                $query->where('is_active', true);
                if ($schedule !== null) {
                    $query->orWhere('id', $schedule->classroom_id);
                }
            })
            ->orderBy('name')
            ->get();

        return [
            'classrooms' => $classrooms,
            'weekdays' => Weekday::cases(),
        ];
    }
}
