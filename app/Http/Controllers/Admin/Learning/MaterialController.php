<?php

namespace App\Http\Controllers\Admin\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Learning\MaterialRequest;
use App\LearningContentStatus;
use App\Models\Classroom;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    /**
     * Disk privat untuk berkas pembelajaran.
     */
    public const DISK = 'local';

    /**
     * Jumlah materi per halaman.
     */
    private const PER_PAGE = 10;

    /**
     * Daftar materi dengan pencarian judul, filter kelas/status, dan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')],
            'status' => ['nullable', Rule::enum(LearningContentStatus::class)],
        ]);

        $materials = Material::query()
            ->with(['classroom.teacher', 'author'])
            ->withCount('assignments')
            ->when(filled($filters['q'] ?? null), fn (Builder $query) => $query->where('title', 'like', '%'.trim($filters['q']).'%'))
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->where('classroom_id', (int) $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.learning.materials.index', [
            'materials' => $materials,
            'filters' => $filters,
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => LearningContentStatus::cases(),
        ]);
    }

    /**
     * Form tambah materi.
     */
    public function create(Request $request): View
    {
        return view('admin.learning.materials.create', [
            'classrooms' => Classroom::query()->where('is_active', true)->with('teacher')->orderBy('name')->get(),
            'statuses' => LearningContentStatus::cases(),
            'preselectedClassroomId' => $request->integer('kelas') ?: null,
        ]);
    }

    /**
     * Menyimpan materi baru beserta berkasnya (disk privat).
     */
    public function store(MaterialRequest $request): RedirectResponse
    {
        $material = new Material($request->safe()->except(['file', 'remove_file']));
        $material->author_id = $request->user()->id;

        if ($request->hasFile('file')) {
            $material->file_path = $request->file('file')->store('learning/materials', self::DISK);
            $material->file_original_name = $request->file('file')->getClientOriginalName();
        }

        $material->save();

        return redirect()
            ->route('admin.learning.materials.show', $material)
            ->with('success', "Materi {$material->title} berhasil disimpan.");
    }

    /**
     * Detail materi.
     */
    public function show(Material $material): View
    {
        $material->load(['classroom.teacher', 'author', 'assignments']);

        return view('admin.learning.materials.show', ['material' => $material]);
    }

    /**
     * Form edit materi.
     */
    public function edit(Material $material): View
    {
        return view('admin.learning.materials.edit', [
            'material' => $material,
            'classrooms' => Classroom::query()
                ->where(fn (Builder $query) => $query->where('is_active', true)->orWhere('id', $material->classroom_id))
                ->with('teacher')
                ->orderBy('name')
                ->get(),
            'statuses' => LearningContentStatus::cases(),
            'preselectedClassroomId' => null,
        ]);
    }

    /**
     * Memperbarui materi. Mengarsipkan dilakukan dengan mengubah status menjadi Diarsipkan.
     */
    public function update(MaterialRequest $request, Material $material): RedirectResponse
    {
        $oldFilePath = null;

        DB::transaction(function () use ($request, $material, &$oldFilePath): void {
            $material->fill($request->safe()->except(['file', 'remove_file']));

            if ($request->hasFile('file') || $request->boolean('remove_file')) {
                $oldFilePath = $material->file_path;
                $material->file_path = null;
                $material->file_original_name = null;
            }

            if ($request->hasFile('file')) {
                $material->file_path = $request->file('file')->store('learning/materials', self::DISK);
                $material->file_original_name = $request->file('file')->getClientOriginalName();
            }

            $material->save();
        });

        if ($oldFilePath !== null) {
            Storage::disk(self::DISK)->delete($oldFilePath);
        }

        return redirect()
            ->route('admin.learning.materials.show', $material)
            ->with('success', "Materi {$material->title} berhasil diperbarui.");
    }
}
