<?php

namespace App\Http\Requests\Admin;

use App\Models\Program;
use App\ProgramLevel;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi form tambah dan edit program bimbel oleh admin.
 */
class ProgramRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Menormalkan nama program dan harga berformat rupiah (mis. "Rp 350.000") menjadi angka.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => preg_replace('/\s+/', ' ', trim((string) $this->input('name'))),
            'price' => $this->normalizePrice($this->input('price')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Program|null $program */
        $program = $this->route('program');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique(Program::class, 'name')->ignore($program)],
            'level' => ['required', Rule::enum(ProgramLevel::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:60'],
            'is_active' => ['required', 'boolean'],
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
            'name.required' => 'Nama program wajib diisi.',
            'name.max' => 'Nama program maksimal 150 karakter.',
            'name.unique' => 'Nama program ini sudah digunakan. Gunakan nama lain.',
            'level.required' => 'Silakan pilih jenjang program.',
            'level.enum' => 'Jenjang program yang dipilih tidak valid.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
            'price.required' => 'Harga program wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka, contoh: 350000.',
            'price.decimal' => 'Harga maksimal memiliki 2 angka di belakang koma.',
            'price.min' => 'Harga tidak boleh negatif.',
            'price.max' => 'Harga terlalu besar.',
            'duration_months.integer' => 'Durasi harus berupa angka bulan, contoh: 6.',
            'duration_months.min' => 'Durasi minimal 1 bulan.',
            'duration_months.max' => 'Durasi maksimal 60 bulan.',
            'is_active.required' => 'Silakan pilih status program.',
            'is_active.boolean' => 'Status program tidak valid.',
        ];
    }

    /**
     * Mengubah input harga seperti "Rp 350.000" atau "350.000,50" menjadi "350000" atau "350000.50".
     *
     * Input yang tidak dikenali dibiarkan apa adanya agar ditolak oleh aturan validasi.
     */
    private function normalizePrice(mixed $price): mixed
    {
        if (! is_string($price)) {
            return $price;
        }

        $price = preg_replace('/^rp\.?\s*/i', '', trim($price));
        $price = str_replace(' ', '', (string) $price);

        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d{1,2})?$/', $price) === 1) {
            return str_replace(['.', ','], ['', '.'], $price);
        }

        if (preg_match('/^\d+,\d{1,2}$/', $price) === 1) {
            return str_replace(',', '.', $price);
        }

        return $price;
    }
}
