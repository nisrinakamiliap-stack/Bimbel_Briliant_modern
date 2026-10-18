<?php

namespace App\Policies;

use App\LearningContentStatus;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\User;
use App\UserRole;

/**
 * Hak akses materi: admin mengelola semua; guru hanya materi kelas yang diampunya;
 * murid hanya dapat melihat materi terbit di kelas tempat ia terdaftar (Diterima).
 */
class MaterialPolicy
{
    /**
     * Melihat materi (termasuk mengunduh berkasnya).
     */
    public function view(User $user, Material $material): bool
    {
        return match ($user->role) {
            UserRole::Admin => true,
            UserRole::Teacher => $material->classroom->teacher_id === $user->id,
            UserRole::Student => $material->status === LearningContentStatus::Published
                && $user->approvedEnrollmentIn($material->classroom_id) !== null,
        };
    }

    /**
     * Membuat materi untuk kelas tertentu.
     */
    public function create(User $user, Classroom $classroom): bool
    {
        return $user->hasRole(UserRole::Admin)
            || ($user->hasRole(UserRole::Teacher) && $classroom->teacher_id === $user->id);
    }

    /**
     * Mengubah atau mengarsipkan materi.
     */
    public function update(User $user, Material $material): bool
    {
        return $this->create($user, $material->classroom);
    }
}
