<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Schedule;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();
        $classroomIds = $student->approvedClassroomIds();

        $classroomFilter = $request->integer('kelas', 0);

        $schedulesQuery = Schedule::query()
            ->whereIn('classroom_id', $classroomIds)
            ->with(['classroom.program', 'classroom.teacher']);

        if ($classroomFilter && in_array($classroomFilter, $classroomIds, true)) {
            $schedulesQuery->where('classroom_id', $classroomFilter);
        }

        $schedules = $schedulesQuery->orderBy('day_of_week')->orderBy('start_time')->get();

        $upcomingOccurrences = $schedules
            ->map(fn (Schedule $s) => ($next = $s->nextOccurrence(now())) === null ? null : [
                'schedule' => $s,
                'next_at' => $next,
            ])
            ->filter()
            ->sortBy('next_at')
            ->values();

        $myClassrooms = Classroom::query()
            ->whereIn('id', $classroomIds)
            ->with('program')
            ->get();

        return view('student.schedules.index', [
            'upcomingOccurrences' => $upcomingOccurrences,
            'allSchedules' => $schedules,
            'myClassrooms' => $myClassrooms,
            'classroomFilter' => $classroomFilter,
            'hasClasses' => $classroomIds !== [],
        ]);
    }
}
