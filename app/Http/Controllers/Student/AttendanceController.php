<?php

namespace App\Http\Controllers\Student;

use App\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();

        $attendances = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->with(['schedule.classroom.program'])
            ->latest('session_date')
            ->paginate(20);

        $summary = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'hadir' => (int) ($summary[AttendanceStatus::Present->value] ?? 0),
            'izin'  => (int) ($summary[AttendanceStatus::Excused->value] ?? 0),
            'sakit' => (int) ($summary[AttendanceStatus::Sick->value] ?? 0),
            'alpa'  => (int) ($summary[AttendanceStatus::Absent->value] ?? 0),
        ];

        return view('student.attendances.index', [
            'attendances' => $attendances,
            'counts' => $counts,
        ]);
    }
}
