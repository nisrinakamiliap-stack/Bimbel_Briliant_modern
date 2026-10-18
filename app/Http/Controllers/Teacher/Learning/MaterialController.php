<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Learning\MaterialRequest;
use App\LearningContentStatus;
use App\Models\Material;
use App\Policies\MaterialPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Materi pembelajaran milik kelas yang diampu guru. Hak akses diperiksa oleh {@see MaterialPolicy}.
 */
class MaterialController extends Controller
{
    /**
     * Jumlah materi per halaman.
     */
    private const PER_PAGE = 10;

    /**
     * Daftar materi kelas yang diampu, dengan pencarian judul dan filter kelas/status.
     */
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')->where('teacher_id', $teacher->id)],
            'status' => ['nullable', Rule::enum(LearningContentStatus::class)],
        ]);

        $materials = Material::query()
            ->whereHas('classroom', fn (Builder $query) => $query->where('teacher_id', $teacher->id))
            ->with('classroom')
            ->withCount('assignments')
            ->when(filled($filters['q'] ?? null), fn (Builder $query) => $query->where('title', 'like', '%'.trim($filters['q']).'%'))
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->where('classroom_id', (int) $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('teacher.learning.materials.index', [
            'materials' => $materials,
            'filters' => $filters,
            'classrooms' => $teacher->taughtClasses()->orderBy('name')->get(['id', 'name']),
            'statuses' => LearningContentStatus::cases(),
        ]);
    }

    /**
     * Form tambah materi untuk kelas aktif yang diampu.
     */
    public function create(Request $request): View
    {
        return view('teacher.learning.materials.create', [
            'classrooms' => $request->user()->taughtClasses()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => LearningContentStatus::cases(),
            'preselectedClassroomId' => $request->integer('kelas') ?: null,
        ]);
    }

    /**
     * Menyimpan materi baru. {@see MaterialRequest} memastikan kelas tujuan diampu guru ini.
     */
    public function store(MaterialRequest $request): RedirectResponse
    {
        $material = new Material($request->safe()->except(['file', 'remove_file']));
        $material->author_id = $request->user()->id;
        $material->replaceFile($request->file('file'));
        $material->save();

        return redirect()
            ->route('teacher.learning.materials.show', $material)
            ->with('success', "Materi {$material->title} berhasil disimpan.");
    }

    /**
     * Detail materi.
     */
    public function show(Material $material): View
    {
        Gate::authorize('view', $material);

        $material->load(['classroom', 'author', 'assignments']);

        return view('teacher.learning.materials.show', ['material' => $material]);
    }

    /**
     * Form edit materi.
     */
    public function edit(Request $request, Material $material): View
    {
        Gate::authorize('update', $material);

        return view('teacher.learning.materials.edit', [
            'material' => $material,
            'classrooms' => $request->user()->taughtClasses()
                ->where(fn (Builder $query) => $query->where('is_active', true)->orWhere('id', $material->classroom_id))
                ->orderBy('name')
                ->get(),
            'statuses' => LearningContentStatus::cases(),
            'preselectedClassroomId' => null,
        ]);
    }

    /**
     * Memperbarui materi. Materi asal dan kelas tujuan harus sama-sama diampu guru ini.
     */
    public function update(MaterialRequest $request, Material $material): RedirectResponse
    {
        Gate::authorize('update', $material);

        $oldFilePath = DB::transaction(function () use ($request, $material): ?string {
            $material->fill($request->safe()->except(['file', 'remove_file']));
            $oldFilePath = $material->replaceFile($request->file('file'), $request->boolean('remove_file'));
            $material->save();

            return $oldFilePath;
        });

        if ($oldFilePath !== null) {
            Storage::disk(Material::FILE_DISK)->delete($oldFilePath);
        }

        return redirect()
            ->route('teacher.learning.materials.show', $material)
            ->with('success', "Materi {$material->title} berhasil diperbarui.");
    }
}
