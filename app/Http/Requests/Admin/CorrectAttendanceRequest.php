<?php

namespace App\Http\Requests\Admin;

use App\AttendanceStatus;
use App\TeacherAttendanceStatus;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi koreksi status absensi murid atau guru oleh admin. Alasan koreksi wajib diisi.
 */
class CorrectAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $statusEnum = $this->isTeacherCorrection() ? TeacherAttendanceStatus::class : AttendanceStatus::class;

        return [
            'status' => ['required', Rule::enum($statusEnum)],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
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
            'status.required' => 'Silakan pilih status kehadiran yang benar.',
            'status.enum' => 'Status kehadiran tidak valid.',
            'reason.required' => 'Alasan koreksi wajib diisi agar perubahan dapat ditelusuri.',
            'reason.min' => 'Alasan koreksi minimal 5 karakter.',
            'reason.max' => 'Alasan koreksi maksimal 500 karakter.',
        ];
    }

    /**
     * Menentukan apakah koreksi ini untuk absensi guru.
     */
    public function isTeacherCorrection(): bool
    {
        return $this->routeIs('admin.attendances.teachers.*');
    }
}
