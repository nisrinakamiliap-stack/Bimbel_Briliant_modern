<?php

namespace App\Http\Controllers\Admin\Learning;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Submission;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    /**
     * Jumlah pengumpulan per halaman.
     */
    private const PER_PAGE = 15;

    /**
     * Daftar pengumpulan dengan filter kelas, tugas, status, dan nama murid.
     *
     * Dipakai juga untuk tab Penilaian (status=belum_dinilai/dinilai).
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')],
            'tugas' => ['nullable', 'integer', Rule::exists('assignments', 'id')],
            'status' => ['nullable', Rule::in(['tepat_waktu', 'terlambat', 'belum_dinilai', 'dinilai'])],
        ]);

        $submissions = $this->filteredQuery($filters)
            ->with(['student', 'assignment.classroom', 'grade'])
            ->latest('submitted_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.learning.submissions.index', [
            'submissions' => $submissions,
            'filters' => $filters,
            'isGradingView' => $request->routeIs('admin.learning.grades.index'),
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name']),
            'assignments' => Assignment::query()->orderByDesc('due_at')->get(['id', 'title']),
        ]);
    }

    /**
     * Tab Penilaian: default menampilkan pengumpulan yang belum dinilai.
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
        $submission->load(['student', 'assignment.classroom.teacher', 'grade.grader']);

        return view('admin.learning.submissions.show', ['submission' => $submission]);
    }

    /**
     * Query pengumpulan yang sudah difilter.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Submission>
     */
    private function filteredQuery(array $filters): Builder
    {
        $search = trim($filters['q'] ?? '');

        return Submission::query()
            ->when($search !== '', fn (Builder $query) => $query->whereHas('student', fn (Builder $query) => $query->withTrashed()->where('name', 'like', "%{$search}%")))
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->whereHas('assignment', fn (Builder $query) => $query->where('classroom_id', (int) $id)))
            ->when($filters['tugas'] ?? null, fn (Builder $query, $id) => $query->where('assignment_id', (int) $id))
            ->when(($filters['status'] ?? null) === 'tepat_waktu', fn (Builder $query) => $query->where('is_late', false))
            ->when(($filters['status'] ?? null) === 'terlambat', fn (Builder $query) => $query->where('is_late', true))
            ->when(($filters['status'] ?? null) === 'belum_dinilai', fn (Builder $query) => $query->doesntHave('grade'))
            ->when(($filters['status'] ?? null) === 'dinilai', fn (Builder $query) => $query->has('grade'));
    }
}
