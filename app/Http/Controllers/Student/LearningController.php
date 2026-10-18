<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Admin\Learning\MaterialController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmissionRequest;
use App\Models\Assignment;
use App\Models\Material;
use App\Models\Submission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Halaman pembelajaran murid: materi dan tugas terbit dari kelas tempat murid terdaftar (Diterima),
 * pengumpulan tugas, serta nilai dan umpan balik miliknya sendiri.
 */
class LearningController extends Controller
{
    /**
     * Daftar materi dan tugas untuk kelas murid.
     */
    public function index(Request $request): View
    {
        $student = $request->user();
        $classroomIds = $student->approvedClassroomIds();

        $assignments = Assignment::query()
            ->published()
            ->whereIn('classroom_id', $classroomIds)
            ->with(['classroom', 'submissions' => fn ($query) => $query->where('student_id', $student->id)->with('grade')])
            ->orderBy('due_at')
            ->get();

        $materials = Material::query()
            ->published()
            ->whereIn('classroom_id', $classroomIds)
            ->with('classroom')
            ->latest('published_at')
            ->get();

        return view('student.learning.index', [
            'assignments' => $assignments,
            'materials' => $materials,
            'hasClasses' => $classroomIds !== [],
        ]);
    }

    /**
     * Detail materi.
     */
    public function material(Material $material): View
    {
        Gate::authorize('view', $material);

        return view('student.learning.material', ['material' => $material->load('classroom')]);
    }

    /**
     * Detail tugas beserta pengumpulan dan nilai milik murid.
     */
    public function assignment(Request $request, Assignment $assignment): View
    {
        Gate::authorize('view', $assignment);

        $submission = $assignment->submissions()->where('student_id', $request->user()->id)->with('grade')->first();

        return view('student.learning.assignment', [
            'assignment' => $assignment->load(['classroom', 'material']),
            'submission' => $submission,
            'submitDecision' => Gate::inspect('submit', $assignment),
        ]);
    }

    /**
     * Mengirim atau memperbarui pengumpulan tugas dengan waktu server.
     */
    public function submit(SubmissionRequest $request, Assignment $assignment): RedirectResponse
    {
        $student = $request->user();
        $enrollment = $student->approvedEnrollmentIn($assignment->classroom_id);
        $file = $request->file('file');
        $newPath = $file?->store('learning/submissions', MaterialController::DISK);

        try {
            [$submission, $replacedPath] = DB::transaction(fn () => Submission::submitFor(
                $assignment,
                $student,
                $enrollment,
                $request->validated('answer_text'),
                $newPath,
                $file?->getClientOriginalName(),
            ));
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk(MaterialController::DISK)->delete($newPath);
            }

            throw $exception;
        }

        if ($replacedPath !== null) {
            Storage::disk(MaterialController::DISK)->delete($replacedPath);
        }

        $message = 'Tugas berhasil dikumpulkan pada '.$submission->submitted_at->translatedFormat('d F Y, H:i').'.';
        if ($submission->is_late) {
            $message .= ' Pengumpulan tercatat terlambat.';
        }

        return redirect()->route('student.learning.assignments.show', $assignment)->with('success', $message);
    }
}
