<?php

namespace App\Http\Controllers\Admin;

use App\EducationDegree;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\TeacherProfile;
use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Jumlah guru per halaman.
     */
    private const PER_PAGE = 10;

    /**
     * Menampilkan daftar guru dengan pencarian, filter bidang keahlian, dan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'bidang' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($filters['q'] ?? '');
        $subject = trim($filters['bidang'] ?? '');

        $teachers = User::query()
            ->teachers()
            ->with('teacherProfile')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($subject !== '', function (Builder $query) use ($subject): void {
                $query->whereRelation('teacherProfile', 'subject', $subject);
            })
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.teachers.index', [
            'teachers' => $teachers,
            'search' => $search,
            'subject' => $subject,
            'subjects' => $this->availableSubjects(),
        ]);
    }

    /**
     * Menampilkan form tambah guru.
     */
    public function create(): View
    {
        return view('admin.teachers.create', [
            'educationDegrees' => EducationDegree::cases(),
            'subjects' => $this->availableSubjects(),
        ]);
    }

    /**
     * Menyimpan akun guru baru beserta profilnya dalam satu transaksi.
     */
    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $teacher = DB::transaction(function () use ($request): User {
            $teacher = new User([
                ...$request->accountData(),
                'password' => $request->validated('password'),
            ]);
            $teacher->role = UserRole::Teacher;
            $teacher->save();

            $teacher->teacherProfile()->create($request->profileData());

            return $teacher;
        });

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', "Guru {$teacher->name} berhasil ditambahkan.");
    }

    /**
     * Menampilkan detail guru.
     */
    public function show(User $teacher): View
    {
        $teacher->load(['teacherProfile', 'taughtClasses' => fn ($query) => $query->with('program')->orderBy('name')]);

        return view('admin.teachers.show', [
            'teacher' => $teacher,
        ]);
    }

    /**
     * Menampilkan form edit guru.
     */
    public function edit(User $teacher): View
    {
        $teacher->load('teacherProfile');

        return view('admin.teachers.edit', [
            'teacher' => $teacher,
            'educationDegrees' => EducationDegree::cases(),
            'subjects' => $this->availableSubjects(),
        ]);
    }

    /**
     * Memperbarui data akun dan profil guru.
     *
     * Role dan password tidak diubah melalui form ini.
     */
    public function update(UpdateTeacherRequest $request, User $teacher): RedirectResponse
    {
        DB::transaction(function () use ($request, $teacher): void {
            $teacher->update($request->accountData());

            $teacher->teacherProfile()->updateOrCreate([], $request->profileData());
        });

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', "Data guru {$teacher->name} berhasil diperbarui.");
    }

    /**
     * Menghapus guru secara soft delete.
     *
     * Data akun dan profil tetap tersimpan agar riwayat mengajar tetap utuh, tetapi akun tidak lagi
     * dapat login dan tidak tampil di daftar guru.
     */
    public function destroy(User $teacher): RedirectResponse
    {
        $teacher->delete();

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', "Guru {$teacher->name} berhasil dihapus. Akun tidak dapat digunakan untuk login lagi.");
    }

    /**
     * Daftar bidang keahlian yang sudah dipakai oleh guru aktif, untuk filter dan saran isian.
     *
     * @return Collection<int, string>
     */
    private function availableSubjects(): Collection
    {
        return TeacherProfile::query()
            ->whereHas('user', fn (Builder $query) => $query->teachers())
            ->distinct()
            ->orderBy('subject')
            ->pluck('subject');
    }
}
