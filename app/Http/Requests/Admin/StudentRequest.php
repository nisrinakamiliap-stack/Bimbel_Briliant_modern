<?php

namespace App\Http\Requests\Admin;

use App\EducationLevel;
use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Aturan validasi bersama untuk form tambah dan edit murid oleh admin.
 *
 * Field `role` tidak pernah divalidasi maupun diambil dari request.
 */
abstract class StudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Menormalkan email ke huruf kecil dan nomor telepon tanpa spasi/tanda hubung.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => $this->normalizePhone($this->input('phone')),
            'parent_phone' => $this->normalizePhone($this->input('parent_phone')),
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
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', $this->uniqueEmailRule()],
            'phone' => ['required', 'string', 'regex:'.User::PHONE_PATTERN],
            'education_level' => ['required', Rule::enum(EducationLevel::class)],
            'school_name' => ['nullable', 'string', 'max:150'],
            'birth_date' => ['nullable', 'date', 'before:today', 'after:1950-01-01'],
            'address' => ['nullable', 'string', 'max:500'],
            'parent_name' => ['nullable', 'string', 'max:100'],
            'parent_phone' => ['nullable', 'string', 'regex:'.User::PHONE_PATTERN],
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
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor telepon tidak valid. Gunakan format nomor HP Indonesia, contoh: 081234567890.',
            'education_level.required' => 'Silakan pilih jenjang pendidikan.',
            'education_level.enum' => 'Jenjang pendidikan yang dipilih tidak valid.',
            'school_name.max' => 'Nama sekolah maksimal 150 karakter.',
            'birth_date.date' => 'Tanggal lahir tidak valid.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'birth_date.after' => 'Tanggal lahir tidak valid.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'parent_name.max' => 'Nama orang tua/wali maksimal 100 karakter.',
            'parent_phone.regex' => 'Nomor telepon orang tua/wali tidak valid. Contoh: 081234567890.',
            'password.required' => 'Password awal wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal :min karakter.',
            'password.letters' => 'Password harus mengandung huruf.',
            'password.numbers' => 'Password harus mengandung angka.',
            'password.mixed' => 'Password harus mengandung huruf besar dan huruf kecil.',
            'password.uncompromised' => 'Password ini pernah bocor di internet. Silakan gunakan password lain.',
        ];
    }

    /**
     * Data akun (tabel users) yang boleh disimpan dari request.
     *
     * @return array{name: string, email: string, phone: string}
     */
    public function accountData(): array
    {
        return $this->safe()->only(['name', 'email', 'phone']);
    }

    /**
     * Data profil murid (tabel student_profiles) yang boleh disimpan dari request.
     *
     * @return array{education_level: string, school_name: string|null, birth_date: string|null, address: string|null, parent_name: string|null, parent_phone: string|null}
     */
    public function profileData(): array
    {
        return $this->safe()->only(['education_level', 'school_name', 'birth_date', 'address', 'parent_name', 'parent_phone']);
    }

    /**
     * Aturan unique email untuk form ini.
     */
    abstract protected function uniqueEmailRule(): Unique;

    /**
     * Membersihkan spasi, tanda hubung, titik, dan tanda kurung dari nomor telepon.
     */
    private function normalizePhone(mixed $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        return preg_replace('/[\s\-().]/', '', (string) $phone);
    }
}
