<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CorrectAttendanceRequest;
use App\Models\Classroom;
use App\Models\TeacherAttendance;
use App\TeacherAttendanceStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherAttendanceController extends Controller
{
    /**
     * Jumlah baris per halaman.
     */
    private const PER_PAGE = 15;

    /**
     * Riwayat check-in/check-out guru dengan pencarian, filter, rekap status, dan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')],
            'status' => ['nullable', Rule::enum(TeacherAttendanceStatus::class)],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir periode tidak boleh lebih awal daripada tanggal awal.',
        ]);

        $statusCounts = $this->filteredQuery($filters, includeStatus: false)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $records = $this->filteredQuery($filters, includeStatus: true)
            ->with(['teacher', 'schedule.classroom'])
            ->orderByDesc('session_date')
            ->orderByDesc('checked_in_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.attendances.teachers.index', [
            'records' => $records,
            'filters' => $filters,
            'statusCounts' => collect(TeacherAttendanceStatus::cases())->mapWithKeys(fn (TeacherAttendanceStatus $status) => [$status->value => (int) ($statusCounts[$status->value] ?? 0)]),
            'statuses' => TeacherAttendanceStatus::cases(),
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Form koreksi status absensi guru. Waktu check-in/check-out tetap tersimpan apa adanya.
     */
    public function edit(TeacherAttendance $teacherAttendance): View
    {
        $teacherAttendance->load(['teacher', 'schedule.classroom', 'corrector']);

        return view('admin.attendances.teachers.edit', [
            'attendance' => $teacherAttendance,
            'statuses' => TeacherAttendanceStatus::cases(),
        ]);
    }

    /**
     * Menyimpan koreksi status absensi guru beserta alasan dan status sebelumnya.
     */
    public function update(CorrectAttendanceRequest $request, TeacherAttendance $teacherAttendance): RedirectResponse
    {
        DB::transaction(fn () => $teacherAttendance->correctStatus(
            TeacherAttendanceStatus::from($request->validated('status')),
            $request->validated('reason'),
            $request->user(),
        ));

        return redirect()
            ->route('admin.attendances.teachers.index')
            ->with('success', "Absensi {$teacherAttendance->teacher->name} pada {$teacherAttendance->session_date->translatedFormat('d F Y')} berhasil dikoreksi menjadi {$teacherAttendance->status->label()}.");
    }

    /**
     * Query absensi guru yang sudah difilter.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<TeacherAttendance>
     */
    private function filteredQuery(array $filters, bool $includeStatus): Builder
    {
        $search = trim($filters['q'] ?? '');

        return TeacherAttendance::query()
            ->when($search !== '', fn (Builder $query) => $query->whereHas('teacher', fn (Builder $query) => $query->withTrashed()->where('name', 'like', "%{$search}%")))
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->whereHas('schedule', fn (Builder $query) => $query->where('classroom_id', (int) $id)))
            ->when($includeStatus && isset($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when($filters['dari'] ?? null, fn (Builder $query, string $date) => $query->where('session_date', '>=', $date))
            ->when($filters['sampai'] ?? null, fn (Builder $query, string $date) => $query->where('session_date', '<=', $date));
    }
}
