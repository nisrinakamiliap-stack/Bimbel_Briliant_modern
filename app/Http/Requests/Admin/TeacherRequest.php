<?php

namespace App\Http\Requests\Admin;

use App\EducationDegree;
use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Aturan validasi bersama untuk form tambah dan edit guru oleh admin.
 *
 * Field `role` tidak pernah divalidasi maupun diambil dari request.
 */
abstract class TeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Menormalkan email, nomor telepon, dan spasi berlebih pada bidang keahlian.
     */
    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');

        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => $phone === null || $phone === '' ? null : preg_replace('/[\s\-().]/', '', (string) $phone),
            'subject' => preg_replace('/\s+/', ' ', trim((string) $this->input('subject'))),
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
            'subject' => ['required', 'string', 'max:100'],
            'last_education' => ['nullable', Rule::enum(EducationDegree::class)],
            'institution' => ['nullable', 'string', 'max:150'],
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
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor telepon tidak valid. Gunakan format nomor HP Indonesia, contoh: 081234567890.',
            'subject.required' => 'Bidang keahlian atau mata pelajaran wajib diisi.',
            'subject.max' => 'Bidang keahlian maksimal 100 karakter.',
            'last_education.enum' => 'Pendidikan terakhir yang dipilih tidak valid.',
            'institution.max' => 'Nama institusi maksimal 150 karakter.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'bio.max' => 'Profil singkat maksimal 1000 karakter.',
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
     * Data profil guru (tabel teacher_profiles) yang boleh disimpan dari request.
     *
     * @return array{subject: string, last_education: string|null, institution: string|null, address: string|null, bio: string|null}
     */
    public function profileData(): array
    {
        return $this->safe()->only(['subject', 'last_education', 'institution', 'address', 'bio']);
    }

    /**
     * Aturan unique email untuk form ini.
     */
    abstract protected function uniqueEmailRule(): Unique;
}
