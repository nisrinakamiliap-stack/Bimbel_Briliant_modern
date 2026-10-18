<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Schedule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user();
        $classroomIds = $student->approvedClassroomIds();

        $upcomingSchedules = [];
        if ($classroomIds !== []) {
            $upcomingSchedules = Schedule::query()
                ->where('is_active', true)
                ->whereIn('classroom_id', $classroomIds)
                ->with(['classroom.program', 'classroom.teacher'])
                ->get()
                ->map(fn (Schedule $schedule) => ($next = $schedule->nextOccurrence(now())) === null ? null : [
                    'schedule' => $schedule,
                    'next_at' => $next,
                ])
                ->filter()
                ->sortBy('next_at')
                ->take(5)
                ->values();
        }

        $recentMaterials = collect();
        if ($classroomIds !== []) {
            $recentMaterials = Material::query()
                ->published()
                ->whereIn('classroom_id', $classroomIds)
                ->with('classroom')
                ->latest('published_at')
                ->take(5)
                ->get();
        }

        return view('student.home', [
            'student' => $student,
            'classroomIds' => $classroomIds,
            'upcomingSchedules' => $upcomingSchedules,
            'recentMaterials' => $recentMaterials,
            'hasClasses' => $classroomIds !== [],
        ]);
    }
}
