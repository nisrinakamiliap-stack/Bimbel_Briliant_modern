<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

class StoreTeacherRequest extends TeacherRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * Akun guru baru membutuhkan password awal agar dapat login; password di-hash oleh model.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Email harus unik di seluruh tabel users, termasuk akun yang sudah dihapus (soft delete).
     */
    protected function uniqueEmailRule(): Unique
    {
        return Rule::unique(User::class, 'email');
    }
}
