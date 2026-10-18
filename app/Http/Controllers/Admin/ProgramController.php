<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProgramRequest;
use App\Models\Program;
use App\ProgramLevel;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramController extends Controller
{
    /**
     * Jumlah program per halaman.
     */
    private const PER_PAGE = 10;

    /**
     * Menampilkan daftar program dengan pencarian nama, filter jenjang/status, dan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'jenjang' => ['nullable', Rule::enum(ProgramLevel::class)],
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
        ]);

        $search = trim($filters['q'] ?? '');
        $level = $filters['jenjang'] ?? null;
        $status = $filters['status'] ?? null;

        $programs = Program::query()
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->when($level, fn (Builder $query, string $level) => $query->where('level', $level))
            ->when($status, fn (Builder $query, string $status) => $query->where('is_active', $status === 'aktif'))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.programs.index', [
            'programs' => $programs,
            'search' => $search,
            'level' => $level,
            'status' => $status,
            'levels' => ProgramLevel::cases(),
        ]);
    }

    /**
     * Menampilkan form tambah program.
     */
    public function create(): View
    {
        return view('admin.programs.create', [
            'levels' => ProgramLevel::cases(),
        ]);
    }

    /**
     * Menyimpan program baru lalu kembali ke daftar program.
     */
    public function store(ProgramRequest $request): RedirectResponse
    {
        $program = Program::create($request->validated());

        return redirect()
            ->route('admin.programs.index')
            ->with('success', "Program {$program->name} berhasil ditambahkan.");
    }

    /**
     * Menampilkan detail program.
     */
    public function show(Program $program): View
    {
        return view('admin.programs.show', [
            'program' => $program,
            'isDeletable' => ! $program->hasDependentRecords(),
        ]);
    }

    /**
     * Menampilkan form edit program.
     */
    public function edit(Program $program): View
    {
        return view('admin.programs.edit', [
            'program' => $program,
            'levels' => ProgramLevel::cases(),
        ]);
    }

    /**
     * Memperbarui data program.
     */
    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        $program->update($request->validated());

        return redirect()
            ->route('admin.programs.show', $program)
            ->with('success', "Program {$program->name} berhasil diperbarui.");
    }

    /**
     * Mengaktifkan atau menonaktifkan program.
     */
    public function toggleStatus(Program $program): RedirectResponse
    {
        $program->is_active = ! $program->is_active;
        $program->save();

        $message = $program->is_active
            ? "Program {$program->name} berhasil diaktifkan."
            : "Program {$program->name} berhasil dinonaktifkan.";

        return back()->with('success', $message);
    }

    /**
     * Menghapus program secara permanen hanya jika belum dipakai oleh data lain.
     *
     * Program yang sudah memiliki kelas, pendaftaran, atau pembayaran tidak dihapus;
     * admin diarahkan untuk menonaktifkannya agar riwayat tetap terjaga.
     */
    public function destroy(Program $program): RedirectResponse
    {
        if ($program->hasDependentRecords()) {
            return back()->with(
                'error',
                "Program {$program->name} tidak dapat dihapus karena sudah digunakan oleh kelas, pendaftaran, atau pembayaran. Nonaktifkan program ini agar tidak ditawarkan lagi."
            );
        }

        $program->delete();

        return redirect()
            ->route('admin.programs.index')
            ->with('success', "Program {$program->name} berhasil dihapus.");
    }
}
