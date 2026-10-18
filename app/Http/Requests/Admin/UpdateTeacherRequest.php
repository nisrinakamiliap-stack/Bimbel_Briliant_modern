<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateTeacherRequest extends TeacherRequest
{
    /**
     * Email harus unik, kecuali email milik guru yang sedang diedit.
     */
    protected function uniqueEmailRule(): Unique
    {
        /** @var User $teacher */
        $teacher = $this->route('teacher');

        return Rule::unique(User::class, 'email')->ignore($teacher);
    }
}
