<?php

namespace App\Http\Requests\Teacher;

use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Perubahan profil oleh guru sendiri. Hanya nomor telepon, alamat, dan bio yang dapat diubah;
 * nama, email, role, dan bidang ajar dikelola admin.
 */
class ProfileRequest extends FormRequest
{
    /**
     * Hanya guru yang dapat mengubah profil guru miliknya.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Teacher) ?? false;
    }

    /**
     * Membersihkan spasi, tanda hubung, dan tanda kurung dari nomor telepon.
     */
    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');

        $this->merge([
            'phone' => blank($phone) ? null : preg_replace('/[\s\-().]/', '', (string) $phone),
            'address' => blank($this->input('address')) ? null : trim((string) $this->input('address')),
            'bio' => blank($this->input('bio')) ? null : trim((string) $this->input('bio')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:'.User::PHONE_PATTERN],
            'address' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor telepon tidak valid. Gunakan format nomor HP Indonesia, contoh: 081234567890.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'bio.max' => 'Bio maksimal 1000 karakter.',
        ];
    }
}
