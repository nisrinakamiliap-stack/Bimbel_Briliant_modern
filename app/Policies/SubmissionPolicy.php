<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use App\UserRole;

/**
 * Hak akses pengumpulan: admin melihat semua; guru hanya pengumpulan kelas yang diampunya;
 * murid hanya pengumpulan miliknya sendiri.
 */
class SubmissionPolicy
{
    /**
     * Melihat pengumpulan (termasuk mengunduh berkas jawaban dan melihat nilainya).
     */
    public function view(User $user, Submission $submission): bool
    {
        return match ($user->role) {
            UserRole::Admin => true,
            UserRole::Teacher => $submission->assignment->classroom->teacher_id === $user->id,
            UserRole::Student => $submission->student_id === $user->id,
        };
    }

    /**
     * Memberi atau mengubah nilai: admin, atau guru pengampu kelas tugas tersebut.
     */
    public function grade(User $user, Submission $submission): bool
    {
        return $user->hasRole(UserRole::Admin)
            || ($user->hasRole(UserRole::Teacher) && $submission->assignment->classroom->teacher_id === $user->id);
    }
}
