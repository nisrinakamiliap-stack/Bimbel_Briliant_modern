<?php

namespace App\Http\Requests\Admin;

use App\PaymentMethod;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi aksi Tandai Dibayar pada honorarium oleh admin.
 */
class MarkHonorariumPaidRequest extends FormRequest
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
        return [
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'paid_on'        => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'notes'          => ['nullable', 'string', 'max:1000'],
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
            'payment_method.required' => 'Silakan pilih metode pembayaran.',
            'payment_method.enum'     => 'Metode pembayaran tidak valid.',
            'paid_on.required'        => 'Tanggal pembayaran wajib diisi.',
            'paid_on.date_format'     => 'Format tanggal tidak valid (YYYY-MM-DD).',
            'paid_on.before_or_equal' => 'Tanggal pembayaran tidak boleh melebihi hari ini.',
            'notes.max'               => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
