<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Learning\AssignmentRequest;
use App\LearningContentStatus;
use App\Models\Assignment;
use App\Models\Material;
use App\Models\User;
use App\Policies\AssignmentPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Tugas milik kelas yang diampu guru. Hak akses diperiksa oleh {@see AssignmentPolicy}.
 */
class AssignmentController extends Controller
{
    /**
     * Jumlah tugas per halaman.
     */
    private const PER_PAGE = 10;

    /**
     * Daftar tugas kelas yang diampu, dengan pencarian judul dan filter kelas/status.
     */
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')->where('teacher_id', $teacher->id)],
            'status' => ['nullable', Rule::enum(LearningContentStatus::class)],
        ]);

        $assignments = Assignment::query()
            ->whereHas('classroom', fn (Builder $query) => $query->where('teacher_id', $teacher->id))
            ->with('classroom')
            ->withCount(['submissions', 'submissions as ungraded_count' => fn (Builder $query) => $query->doesntHave('grade')])
            ->when(filled($filters['q'] ?? null), fn (Builder $query) => $query->where('title', 'like', '%'.trim($filters['q']).'%'))
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->where('classroom_id', (int) $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByDesc('due_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('teacher.learning.assignments.index', [
            'assignments' => $assignments,
            'filters' => $filters,
            'classrooms' => $teacher->taughtClasses()->orderBy('name')->get(['id', 'name']),
            'statuses' => LearningContentStatus::cases(),
        ]);
    }

    /**
     * Form tambah tugas.
     */
    public function create(Request $request): View
    {
        return view('teacher.learning.assignments.create', [
            ...$this->formOptions($request->user(), null),
            'preselectedClassroomId' => $request->integer('kelas') ?: null,
        ]);
    }

    /**
     * Menyimpan tugas baru. {@see AssignmentRequest} memastikan kelas tujuan diampu guru ini.
     */
    public function store(AssignmentRequest $request): RedirectResponse
    {
        $assignment = new Assignment($this->attributesFrom($request));
        $assignment->author_id = $request->user()->id;
        $assignment->replaceFile($request->file('attachment'));
        $assignment->save();

        return redirect()
            ->route('teacher.learning.assignments.show', $assignment)
            ->with('success', "Tugas {$assignment->title} berhasil disimpan.");
    }

    /**
     * Detail tugas beserta murid yang sudah dan belum mengumpulkan.
     */
    public function show(Assignment $assignment): View
    {
        Gate::authorize('view', $assignment);

        $assignment->load(['classroom', 'material']);

        return view('teacher.learning.assignments.show', [
            'assignment' => $assignment,
            'roster' => $assignment->participantEnrollments(),
            'submissions' => $assignment->submissions()->with(['student', 'grade'])->get()->keyBy('student_id'),
            'canDelete' => Gate::inspect('delete', $assignment),
        ]);
    }

    /**
     * Form edit tugas.
     */
    public function edit(Request $request, Assignment $assignment): View
    {
        Gate::authorize('update', $assignment);

        return view('teacher.learning.assignments.edit', [
            'assignment' => $assignment,
            ...$this->formOptions($request->user(), $assignment),
            'preselectedClassroomId' => null,
        ]);
    }

    /**
     * Memperbarui tugas. Mengarsipkan dilakukan dengan mengubah status menjadi Diarsipkan.
     */
    public function update(AssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        Gate::authorize('update', $assignment);

        $oldPath = DB::transaction(function () use ($request, $assignment): ?string {
            $assignment->fill($this->attributesFrom($request));
            $oldPath = $assignment->replaceFile($request->file('attachment'), $request->boolean('remove_attachment'));
            $assignment->save();

            return $oldPath;
        });

        if ($oldPath !== null) {
            Storage::disk(Assignment::FILE_DISK)->delete($oldPath);
        }

        return redirect()
            ->route('teacher.learning.assignments.show', $assignment)
            ->with('success', "Tugas {$assignment->title} berhasil diperbarui.");
    }

    /**
     * Menghapus tugas yang belum memiliki pengumpulan sama sekali.
     */
    public function destroy(Assignment $assignment): RedirectResponse
    {
        $decision = Gate::inspect('delete', $assignment);

        if ($decision->denied()) {
            abort_unless(Gate::allows('update', $assignment), 403, $decision->message());

            return back()->with('error', $decision->message());
        }

        $attachmentPath = $assignment->attachment_path;
        $assignment->delete();

        if ($attachmentPath !== null) {
            Storage::disk(Assignment::FILE_DISK)->delete($attachmentPath);
        }

        return redirect()
            ->route('teacher.learning.assignments.index')
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
     * Pilihan kelas (yang diampu) dan materi untuk form tugas.
     *
     * @return array<string, mixed>
     */
    private function formOptions(User $teacher, ?Assignment $assignment): array
    {
        $classrooms = $teacher->taughtClasses()
            ->where(fn (Builder $query) => $query->where('is_active', true)
                ->when($assignment, fn (Builder $query) => $query->orWhere('id', $assignment->classroom_id)))
            ->orderBy('name')
            ->get();

        return [
            'classrooms' => $classrooms,
            'materials' => Material::query()
                ->whereIn('classroom_id', $classrooms->modelKeys())
                ->where('status', '!=', LearningContentStatus::Archived->value)
                ->orderBy('title')
                ->get(['id', 'title', 'classroom_id']),
            'statuses' => LearningContentStatus::cases(),
        ];
    }
}
