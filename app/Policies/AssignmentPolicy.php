<?php

namespace App\Policies;

use App\LearningContentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Access\Response;

/**
 * Hak akses tugas: admin mengelola semua; guru hanya tugas kelas yang diampunya;
 * murid hanya dapat melihat dan mengumpulkan tugas terbit di kelas tempat ia terdaftar (Diterima).
 */
class AssignmentPolicy
{
    /**
     * Melihat tugas (termasuk mengunduh lampirannya).
     */
    public function view(User $user, Assignment $assignment): bool
    {
        return match ($user->role) {
            UserRole::Admin => true,
            UserRole::Teacher => $assignment->classroom->teacher_id === $user->id,
            UserRole::Student => $assignment->status === LearningContentStatus::Published
                && $user->approvedEnrollmentIn($assignment->classroom_id) !== null,
        };
    }

    /**
     * Membuat tugas untuk kelas tertentu.
     */
    public function create(User $user, Classroom $classroom): bool
    {
        return $user->hasRole(UserRole::Admin)
            || ($user->hasRole(UserRole::Teacher) && $classroom->teacher_id === $user->id);
    }

    /**
     * Mengubah atau mengarsipkan tugas.
     */
    public function update(User $user, Assignment $assignment): bool
    {
        return $this->create($user, $assignment->classroom);
    }

    /**
     * Menghapus tugas: hanya jika belum ada pengumpulan sama sekali.
     */
    public function delete(User $user, Assignment $assignment): Response
    {
        if (! $this->update($user, $assignment)) {
            return Response::deny('Anda tidak berwenang mengelola tugas ini.');
        }

        return $assignment->hasSubmissions()
            ? Response::deny('Tugas yang sudah memiliki pengumpulan tidak dapat dihapus. Arsipkan tugas ini.')
            : Response::allow();
    }

    /**
     * Mengirim atau memperbarui pengumpulan tugas oleh murid, pada waktu server saat ini.
     */
    public function submit(User $user, Assignment $assignment): Response
    {
        if (! $user->hasRole(UserRole::Student) || ! $this->view($user, $assignment)) {
            return Response::deny('Tugas ini tidak ditujukan untuk kelas Anda.');
        }

        if (! $assignment->acceptsSubmissionsAt(now())) {
            return Response::deny('Tenggat tugas sudah lewat dan tugas ini tidak menerima pengumpulan terlambat.');
        }

        $existing = $assignment->submissions()->where('student_id', $user->id)->with('grade')->first();

        if ($existing !== null) {
            if ($existing->grade !== null) {
                return Response::deny('Pengumpulan Anda sudah dinilai sehingga tidak dapat diubah lagi.');
            }

            if (! $assignment->allow_resubmission) {
                return Response::deny('Tugas ini tidak mengizinkan pengumpulan diperbarui.');
            }
        }

        return Response::allow();
    }
}
