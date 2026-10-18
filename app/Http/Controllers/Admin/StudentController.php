<?php

namespace App\Http\Controllers\Admin;

use App\EducationLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Jumlah murid per halaman.
     */
    private const PER_PAGE = 10;

    /**
     * Menampilkan daftar murid dengan pencarian, filter jenjang, dan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'jenjang' => ['nullable', Rule::enum(EducationLevel::class)],
        ]);

        $search = trim($filters['q'] ?? '');
        $educationLevel = $filters['jenjang'] ?? null;

        $students = User::query()
            ->students()
            ->with('studentProfile')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($educationLevel, function (Builder $query, string $educationLevel): void {
                $query->whereRelation('studentProfile', 'education_level', $educationLevel);
            })
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'search' => $search,
            'educationLevel' => $educationLevel,
            'educationLevels' => EducationLevel::cases(),
        ]);
    }

    /**
     * Menampilkan form tambah murid.
     */
    public function create(): View
    {
        return view('admin.students.create', [
            'educationLevels' => EducationLevel::cases(),
        ]);
    }

    /**
     * Menyimpan akun murid baru beserta profilnya dalam satu transaksi.
     */
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $student = DB::transaction(function () use ($request): User {
            $student = new User([
                ...$request->accountData(),
                'password' => $request->validated('password'),
            ]);
            $student->role = UserRole::Student;
            $student->save();

            $student->studentProfile()->create($request->profileData());

            return $student;
        });

        return redirect()
            ->route('admin.students.show', $student)
            ->with('success', "Murid {$student->name} berhasil ditambahkan.");
    }

    /**
     * Menampilkan detail murid.
     */
    public function show(User $student): View
    {
        $student->load([
            'studentProfile',
            'enrollments' => fn ($query) => $query->with(['program', 'classroom'])->latest('enrolled_on'),
        ]);

        return view('admin.students.show', [
            'student' => $student,
        ]);
    }

    /**
     * Menampilkan form edit murid.
     */
    public function edit(User $student): View
    {
        $student->load('studentProfile');

        return view('admin.students.edit', [
            'student' => $student,
            'educationLevels' => EducationLevel::cases(),
        ]);
    }

    /**
     * Memperbarui data akun dan profil murid.
     *
     * Role dan password tidak diubah melalui form ini.
     */
    public function update(UpdateStudentRequest $request, User $student): RedirectResponse
    {
        DB::transaction(function () use ($request, $student): void {
            $student->update($request->accountData());

            $student->studentProfile()->updateOrCreate([], $request->profileData());
        });

        return redirect()
            ->route('admin.students.show', $student)
            ->with('success', "Data murid {$student->name} berhasil diperbarui.");
    }

    /**
     * Menghapus murid secara soft delete.
     *
     * Data akun dan profil tetap tersimpan untuk menjaga riwayat, tetapi akun tidak lagi dapat login
     * dan tidak tampil di daftar murid.
     */
    public function destroy(User $student): RedirectResponse
    {
        $student->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('success', "Murid {$student->name} berhasil dihapus. Akun tidak dapat digunakan untuk login lagi.");
    }
}
