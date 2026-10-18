<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssignmentGrade;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();
        $classroomIds = $student->approvedClassroomIds();

        $grades = AssignmentGrade::query()
            ->whereHas('submission', fn ($q) => $q->where('student_id', $student->id))
            ->with([
                'submission.assignment.classroom',
                'submission.assignment.classroom.teacher',
                'grader',
            ])
            ->whereHas('submission.assignment', fn ($q) => $q->whereIn('classroom_id', $classroomIds))
            ->latest()
            ->paginate(20);

        return view('student.grades.index', [
            'grades' => $grades,
            'hasClasses' => $classroomIds !== [],
        ]);
    }
}
