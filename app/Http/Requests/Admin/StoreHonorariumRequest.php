<?php

namespace App\Http\Requests\Admin;

use App\Models\TeacherHonorarium;
use App\PaymentMethod;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi pencatatan honorarium guru oleh admin.
 */
class StoreHonorariumRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Normalkan nominal agar strip ribuan bisa diterima.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'rate_per_session' => TeacherHonorarium::normalizeRupiahInput($this->input('rate_per_session')),
            'adjustments'      => $this->filled('adjustments') ? TeacherHonorarium::normalizeRupiahInput($this->input('adjustments')) : 0,
            'deductions'       => $this->filled('deductions') ? TeacherHonorarium::normalizeRupiahInput($this->input('deductions')) : 0,
            'notes'            => blank($this->input('notes')) ? null : trim((string) $this->input('notes')),
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
            'teacher_id'       => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'teacher')],
            'period_month'     => ['required', 'integer', 'min:1', 'max:12'],
            'period_year'      => ['required', 'integer', 'min:2020', 'max:2099'],
            'session_count'    => ['required', 'integer', 'min:0', 'max:9999'],
            'rate_per_session' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'adjustments'      => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'deductions'       => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'notes'            => ['nullable', 'string', 'max:1000'],
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
            'teacher_id.required'       => 'Silakan pilih guru.',
            'teacher_id.exists'         => 'Guru tidak ditemukan atau bukan akun guru.',
            'period_month.required'     => 'Bulan periode wajib diisi.',
            'period_month.min'          => 'Bulan tidak valid.',
            'period_month.max'          => 'Bulan tidak valid.',
            'period_year.required'      => 'Tahun periode wajib diisi.',
            'period_year.min'           => 'Tahun minimal 2020.',
            'period_year.max'           => 'Tahun tidak valid.',
            'session_count.required'    => 'Jumlah sesi wajib diisi.',
            'session_count.min'         => 'Jumlah sesi tidak boleh negatif.',
            'rate_per_session.required' => 'Tarif per sesi wajib diisi.',
            'rate_per_session.numeric'  => 'Tarif harus berupa angka.',
            'rate_per_session.min'      => 'Tarif tidak boleh negatif.',
            'notes.max'                 => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
