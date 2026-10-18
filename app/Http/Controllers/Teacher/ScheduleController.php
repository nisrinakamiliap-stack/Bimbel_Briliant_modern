<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\ListsTeachingSessions;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Jadwal mengajar guru yang sedang login, tampilan harian atau mingguan, dengan check-in/check-out.
 */
class ScheduleController extends Controller
{
    use ListsTeachingSessions;

    /**
     * Daftar sesi mengajar.
     */
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $filters = $request->validate([
            'tampilan' => ['nullable', Rule::in(['harian', 'mingguan'])],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')->where('teacher_id', $teacher->id)],
        ], [
            'kelas.exists' => 'Kelas tidak ditemukan di daftar kelas Anda.',
        ]);

        $view = $filters['tampilan'] ?? 'mingguan';
        $date = CarbonImmutable::parse($filters['tanggal'] ?? CarbonImmutable::now()->format('Y-m-d'))->startOfDay();
        $classroomId = isset($filters['kelas']) ? (int) $filters['kelas'] : null;

        [$from, $to, $previous, $next] = $view === 'harian'
            ? [$date, $date, $date->subDay(), $date->addDay()]
            : [$date->startOfWeek(), $date->endOfWeek()->startOfDay(), $date->subWeek(), $date->addWeek()];

        $sessions = $this->teachingSessions($teacher, $from, $to, $classroomId);

        return view('teacher.schedules.index', [
            'view' => $view,
            'date' => $date,
            'from' => $from,
            'to' => $to,
            'previousDate' => $previous,
            'nextDate' => $next,
            'classroomId' => $classroomId,
            'sessions' => $sessions,
            'sessionsByDay' => $sessions->groupBy(fn (array $session) => $session['date']->format('Y-m-d')),
            'classrooms' => $teacher->taughtClasses()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
