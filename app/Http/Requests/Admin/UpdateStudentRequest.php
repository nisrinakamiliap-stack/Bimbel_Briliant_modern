<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateStudentRequest extends StudentRequest
{
    /**
     * Email harus unik, kecuali email milik murid yang sedang diedit.
     */
    protected function uniqueEmailRule(): Unique
    {
        /** @var User $student */
        $student = $this->route('student');

        return Rule::unique(User::class, 'email')->ignore($student);
    }
}
