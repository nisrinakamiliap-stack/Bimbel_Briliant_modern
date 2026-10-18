<?php

namespace App\Http\Requests\Admin;

use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi verifikasi atau penolakan pembayaran. Alasan wajib diisi saat menolak.
 */
class ProcessPaymentRequest extends FormRequest
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
            'reason' => [$this->isRejection() ? 'required' : 'nullable', 'string', 'max:1000'],
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
            'reason.required' => 'Alasan penolakan wajib diisi agar dapat disampaikan kepada murid.',
            'reason.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }

    /**
     * Menentukan apakah permintaan ini adalah penolakan pembayaran.
     */
    public function isRejection(): bool
    {
        return $this->routeIs('admin.payments.reject');
    }
}
