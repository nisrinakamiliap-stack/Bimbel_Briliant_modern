<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClassroomController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();
        $classroomIds = $student->approvedClassroomIds();

        $classrooms = Classroom::query()
            ->whereIn('id', $classroomIds)
            ->with(['program', 'teacher', 'schedules' => fn ($q) => $q->where('is_active', true)->orderBy('day_of_week')->orderBy('start_time')])
            ->get();

        return view('student.classes.index', [
            'classrooms' => $classrooms,
            'hasClasses' => $classroomIds !== [],
        ]);
    }

    public function show(Request $request, Classroom $classroom): View
    {
        $student = $request->user();

        if (! in_array($classroom->id, $student->approvedClassroomIds(), true)) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak terdaftar di kelas ini.');
        }

        $classroom->load(['program', 'teacher', 'schedules' => fn ($q) => $q->orderBy('day_of_week')->orderBy('start_time')]);

        $materials = Material::query()
            ->published()
            ->where('classroom_id', $classroom->id)
            ->latest('published_at')
            ->get();

        return view('student.classes.show', [
            'classroom' => $classroom,
            'materials' => $materials,
        ]);
    }
}
