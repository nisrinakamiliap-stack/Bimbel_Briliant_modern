<?php

namespace App\Http\Controllers\Teacher;

use App\AttendanceStatus;
use App\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\StudentAttendance;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Kelas yang diampu guru yang sedang login. Kelas milik guru lain ditolak dengan 403.
 */
class ClassroomController extends Controller
{
    /**
     * Daftar kelas yang diampu.
     */
    public function index(Request $request): View
    {
        $classrooms = $request->user()->taughtClasses()
            ->with(['program', 'schedules' => fn ($query) => $query->where('is_active', true)->orderBy('day_of_week')->orderBy('start_time')])
            ->withCount(['enrollments as student_count' => fn (Builder $query) => $query->where('status', EnrollmentStatus::Approved->value)])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('teacher.classes.index', ['classrooms' => $classrooms]);
    }

    /**
     * Detail kelas: murid, jadwal, materi, tugas, dan rekap absensi.
     */
    public function show(Request $request, Classroom $classroom): View
    {
        abort_unless($classroom->teacher_id === $request->user()->id, 403, 'Kelas ini tidak diampu oleh Anda.');

        $classroom->load([
            'program',
            'schedules' => fn ($query) => $query->orderBy('day_of_week')->orderBy('start_time'),
            'materials' => fn ($query) => $query->latest()->take(5),
            'assignments' => fn ($query) => $query->withCount('submissions')->orderByDesc('due_at')->take(5),
        ]);

        $enrollments = Enrollment::query()
            ->where('classroom_id', $classroom->id)
            ->where('status', EnrollmentStatus::Approved->value)
            ->with('student')
            ->get()
            ->sortBy(fn (Enrollment $enrollment) => $enrollment->student->name)
            ->values();

        $attendanceCounts = StudentAttendance::query()
            ->whereHas('schedule', fn (Builder $query) => $query->where('classroom_id', $classroom->id))
            ->selectRaw('student_id, status, count(*) as total')
            ->groupBy('student_id', 'status')
            ->toBase()
            ->get()
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->pluck('total', 'status')->map(fn ($total) => (int) $total));

        return view('teacher.classes.show', [
            'classroom' => $classroom,
            'enrollments' => $enrollments,
            'attendanceCounts' => $attendanceCounts,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }
}
