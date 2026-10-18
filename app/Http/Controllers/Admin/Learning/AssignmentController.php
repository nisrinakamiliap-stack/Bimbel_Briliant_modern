<?php

namespace App\Http\Controllers\Admin\Learning;

use App\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Learning\AssignmentRequest;
use App\LearningContentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Material;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    /**
     * Jumlah tugas per halaman.
     */
    private const PER_PAGE = 10;

    /**
     * Daftar tugas dengan pencarian judul, filter kelas/status, dan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')],
            'status' => ['nullable', Rule::enum(LearningContentStatus::class)],
        ]);

        $assignments = Assignment::query()
            ->with(['classroom.teacher'])
            ->withCount('submissions')
            ->when(filled($filters['q'] ?? null), fn (Builder $query) => $query->where('title', 'like', '%'.trim($filters['q']).'%'))
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->where('classroom_id', (int) $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByDesc('due_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.learning.assignments.index', [
            'assignments' => $assignments,
            'filters' => $filters,
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => LearningContentStatus::cases(),
        ]);
    }

    /**
     * Form tambah tugas.
     */
    public function create(Request $request): View
    {
        return view('admin.learning.assignments.create', [
            ...$this->formOptions(null),
            'preselectedClassroomId' => $request->integer('kelas') ?: null,
        ]);
    }

    /**
     * Menyimpan tugas baru beserta lampirannya (disk privat).
     */
    public function store(AssignmentRequest $request): RedirectResponse
    {
        $assignment = new Assignment($this->attributesFrom($request));
        $assignment->author_id = $request->user()->id;

        if ($request->hasFile('attachment')) {
            $assignment->attachment_path = $request->file('attachment')->store('learning/assignments', MaterialController::DISK);
            $assignment->attachment_original_name = $request->file('attachment')->getClientOriginalName();
        }

        $assignment->save();

        return redirect()
            ->route('admin.learning.assignments.show', $assignment)
            ->with('success', "Tugas {$assignment->title} berhasil disimpan.");
    }

    /**
     * Detail tugas beserta daftar murid yang sudah dan belum mengumpulkan.
     */
    public function show(Assignment $assignment): View
    {
        $assignment->load(['classroom.teacher', 'material', 'author']);

        $submissions = $assignment->submissions()->with(['student', 'grade'])->get()->keyBy('student_id');
        $roster = Enrollment::query()
            ->where('classroom_id', $assignment->classroom_id)
            ->where('status', EnrollmentStatus::Approved->value)
            ->with('student')
            ->get()
            ->sortBy(fn (Enrollment $enrollment) => $enrollment->student->name)
            ->values();

        return view('admin.learning.assignments.show', [
            'assignment' => $assignment,
            'roster' => $roster,
            'submissions' => $submissions,
            'canDelete' => Gate::inspect('delete', $assignment),
        ]);
    }

    /**
     * Form edit tugas.
     */
    public function edit(Assignment $assignment): View
    {
        return view('admin.learning.assignments.edit', [
            'assignment' => $assignment,
            ...$this->formOptions($assignment),
            'preselectedClassroomId' => null,
        ]);
    }

    /**
     * Memperbarui tugas. Mengarsipkan dilakukan dengan mengubah status menjadi Diarsipkan.
     */
    public function update(AssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $oldPath = null;

        DB::transaction(function () use ($request, $assignment, &$oldPath): void {
            $assignment->fill($this->attributesFrom($request));

            if ($request->hasFile('attachment') || $request->boolean('remove_attachment')) {
                $oldPath = $assignment->attachment_path;
                $assignment->attachment_path = null;
                $assignment->attachment_original_name = null;
            }

            if ($request->hasFile('attachment')) {
                $assignment->attachment_path = $request->file('attachment')->store('learning/assignments', MaterialController::DISK);
                $assignment->attachment_original_name = $request->file('attachment')->getClientOriginalName();
            }

            $assignment->save();
        });

        if ($oldPath !== null) {
            Storage::disk(MaterialController::DISK)->delete($oldPath);
        }

        return redirect()
            ->route('admin.learning.assignments.show', $assignment)
            ->with('success', "Tugas {$assignment->title} berhasil diperbarui.");
    }

    /**
     * Menghapus tugas yang belum memiliki pengumpulan sama sekali.
     */
    public function destroy(Assignment $assignment): RedirectResponse
    {
        $decision = Gate::inspect('delete', $assignment);

        if ($decision->denied()) {
            return back()->with('error', $decision->message());
        }

        $attachmentPath = $assignment->attachment_path;
        $assignment->delete();

        if ($attachmentPath !== null) {
            Storage::disk(MaterialController::DISK)->delete($attachmentPath);
        }

        return redirect()
            ->route('admin.learning.assignments.index')
            ->with('success', "Tugas {$assignment->title} berhasil dihapus.");
    }

    /**
     * Atribut tugas dari request tervalidasi, dengan tenggat dikonversi ke zona waktu aplikasi.
     *
     * @return array<string, mixed>
     */
    private function attributesFrom(AssignmentRequest $request): array
    {
        $attributes = $request->safe()->except(['attachment', 'remove_attachment']);
        $attributes['due_at'] = CarbonImmutable::createFromFormat(AssignmentRequest::DUE_AT_FORMAT, $attributes['due_at']);

        return $attributes;
    }

    /**
     * Pilihan kelas dan materi untuk form tugas.
     *
     * @return array<string, mixed>
     */
    private function formOptions(?Assignment $assignment): array
    {
        return [
            'classrooms' => Classroom::query()
                ->where(fn (Builder $query) => $query->where('is_active', true)
                    ->when($assignment, fn (Builder $query) => $query->orWhere('id', $assignment->classroom_id)))
                ->with('teacher')
                ->orderBy('name')
                ->get(),
            'materials' => Material::query()->where('status', '!=', LearningContentStatus::Archived->value)->orderBy('title')->get(['id', 'title', 'classroom_id']),
            'statuses' => LearningContentStatus::cases(),
        ];
    }
}
