<?php

namespace App\Http\Requests\Admin;

use App\Models\TeacherHonorarium;
use App\PaymentMethod;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi pencatatan transaksi pembayaran honorarium oleh admin.
 */
class RecordHonorariumPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Normalkan input nominal rupiah.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount'           => TeacherHonorarium::normalizeRupiahInput($this->input('amount')),
            'reference_number' => blank($this->input('reference_number')) ? null : trim((string) $this->input('reference_number')),
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
            'amount'           => ['required', 'numeric', 'decimal:0,2', 'min:1'],
            'payment_method'   => ['required', Rule::enum(PaymentMethod::class)],
            'paid_on'          => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'proof'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
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
            'amount.required'         => 'Nominal pembayaran wajib diisi.',
            'amount.numeric'          => 'Nominal pembayaran harus berupa angka.',
            'amount.min'              => 'Nominal pembayaran minimal Rp1.',
            'payment_method.required' => 'Silakan pilih metode pembayaran.',
            'payment_method.enum'     => 'Metode pembayaran tidak valid.',
            'paid_on.required'        => 'Tanggal pembayaran wajib diisi.',
            'paid_on.date_format'     => 'Format tanggal tidak valid (YYYY-MM-DD).',
            'paid_on.before_or_equal' => 'Tanggal pembayaran tidak boleh melebihi hari ini.',
            'reference_number.max'    => 'Nomor referensi maksimal 100 karakter.',
            'proof.file'              => 'Bukti pembayaran harus berupa berkas yang valid.',
            'proof.mimes'             => 'Format bukti pembayaran harus JPG, PNG, atau PDF.',
            'proof.max'               => 'Ukuran bukti pembayaran maksimal 2 MB.',
            'notes.max'               => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
