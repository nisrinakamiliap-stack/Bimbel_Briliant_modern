<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\GradeController;
use App\Models\Assignment;
use App\Models\Submission;
use App\Policies\SubmissionPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Pengumpulan tugas dari kelas yang diampu guru, termasuk halaman Penilaian.
 *
 * Penyimpanan nilai ditangani {@see GradeController}; hak akses oleh {@see SubmissionPolicy}.
 */
class SubmissionController extends Controller
{
    /**
     * Jumlah pengumpulan per halaman.
     */
    private const PER_PAGE = 15;

    /**
     * Daftar pengumpulan dengan filter kelas, tugas, status, dan nama murid.
     */
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')->where('teacher_id', $teacher->id)],
            'tugas' => ['nullable', 'integer', Rule::exists('assignments', 'id')],
            'status' => ['nullable', Rule::in(['tepat_waktu', 'terlambat', 'belum_dinilai', 'dinilai'])],
        ]);

        $search = trim($filters['q'] ?? '');

        $submissions = Submission::query()
            ->whereHas('assignment.classroom', fn (Builder $query) => $query->where('teacher_id', $teacher->id))
            ->when($search !== '', fn (Builder $query) => $query->whereHas('student', fn (Builder $query) => $query->withTrashed()->where('name', 'like', "%{$search}%")))
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->whereHas('assignment', fn (Builder $query) => $query->where('classroom_id', (int) $id)))
            ->when($filters['tugas'] ?? null, fn (Builder $query, $id) => $query->where('assignment_id', (int) $id))
            ->when(($filters['status'] ?? null) === 'tepat_waktu', fn (Builder $query) => $query->where('is_late', false))
            ->when(($filters['status'] ?? null) === 'terlambat', fn (Builder $query) => $query->where('is_late', true))
            ->when(($filters['status'] ?? null) === 'belum_dinilai', fn (Builder $query) => $query->doesntHave('grade'))
            ->when(($filters['status'] ?? null) === 'dinilai', fn (Builder $query) => $query->has('grade'))
            ->with(['student', 'assignment.classroom', 'grade'])
            ->latest('submitted_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('teacher.learning.submissions.index', [
            'submissions' => $submissions,
            'filters' => $filters,
            'isGradingView' => $request->routeIs('teacher.learning.grades.index'),
            'classrooms' => $teacher->taughtClasses()->orderBy('name')->get(['id', 'name']),
            'assignments' => Assignment::query()
                ->whereHas('classroom', fn (Builder $query) => $query->where('teacher_id', $teacher->id))
                ->orderByDesc('due_at')
                ->get(['id', 'title']),
        ]);
    }

    /**
     * Halaman Penilaian: default menampilkan pengumpulan yang belum dinilai.
     */
    public function grading(Request $request): View
    {
        if (! $request->has('status')) {
            $request->merge(['status' => 'belum_dinilai']);
        }

        return $this->index($request);
    }

    /**
     * Detail pengumpulan beserta form penilaian.
     */
    public function show(Submission $submission): View
    {
        Gate::authorize('view', $submission);

        $submission->load(['student', 'assignment.classroom', 'grade.grader']);

        return view('teacher.learning.submissions.show', ['submission' => $submission]);
    }
}
