<?php

namespace App\Http\Requests\Admin;

use App\Models\Enrollment;
use App\Models\Payment;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi form tambah dan edit pendaftaran murid.
 *
 * Saat mengedit, hanya field yang diizinkan oleh status pendaftaran yang divalidasi dan disimpan
 * (lihat {@see Enrollment::editableFields()}). Murid tidak dapat diganti setelah pendaftaran dibuat.
 * Pendaftaran ganda dan kapasitas kelas diperiksa ulang di dalam transaksi oleh controller.
 */
class EnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Kelas kosong diartikan "belum ditentukan".
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'classroom_id' => blank($this->input('classroom_id')) ? null : $this->input('classroom_id'),
            'admin_notes' => blank($this->input('admin_notes')) ? null : trim((string) $this->input('admin_notes')),
        ]);

        if ($this->has('bill_amount')) {
            $this->merge(['bill_amount' => Payment::normalizeRupiahInput($this->input('bill_amount'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $enrollment = $this->enrollment();

        $programId = $enrollment !== null && ! in_array('program_id', $enrollment->editableFields(), true)
            ? $enrollment->program_id
            : $this->input('program_id');

        $programExists = Rule::exists('programs', 'id');
        if ((string) $programId !== (string) $enrollment?->program_id) {
            $programExists->where('is_active', true);
        }

        $classroomExists = Rule::exists('classes', 'id')->where('program_id', (int) $programId);
        if ((string) $this->input('classroom_id') !== (string) $enrollment?->classroom_id) {
            $classroomExists->where('is_active', true);
        }

        $rules = [
            'student_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::Student->value)->withoutTrashed(),
            ],
            'program_id' => ['required', 'integer', $programExists],
            'classroom_id' => ['nullable', 'integer', $classroomExists],
            'enrolled_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];

        if ($enrollment === null) {
            return $rules;
        }

        $paidAmount = $enrollment->paidCents() / 100;
        $rules['bill_amount'] = ['sometimes', 'required', 'numeric', 'decimal:0,2', 'min:'.$paidAmount, 'max:9999999999.99'];

        return array_intersect_key($rules, array_flip($enrollment->editableFields()));
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Silakan pilih murid.',
            'student_id.integer' => 'Murid yang dipilih tidak valid.',
            'student_id.exists' => 'Murid tidak ditemukan atau akunnya sudah dihapus.',
            'program_id.required' => 'Silakan pilih program bimbel.',
            'program_id.integer' => 'Program bimbel yang dipilih tidak valid.',
            'program_id.exists' => 'Program bimbel tidak ditemukan atau sedang tidak menerima pendaftaran.',
            'classroom_id.integer' => 'Kelas yang dipilih tidak valid.',
            'classroom_id.exists' => 'Kelas tidak ditemukan, tidak aktif, atau bukan bagian dari program yang dipilih.',
            'bill_amount.required' => 'Total tagihan wajib diisi.',
            'bill_amount.numeric' => 'Total tagihan harus berupa angka, contoh: 350000.',
            'bill_amount.decimal' => 'Total tagihan maksimal memiliki 2 angka di belakang koma.',
            'bill_amount.min' => 'Total tagihan tidak boleh kurang dari pembayaran yang sudah berhasil ('.Payment::rupiah($this->enrollment()?->paidCents() / 100).').',
            'bill_amount.max' => 'Total tagihan terlalu besar.',
            'enrolled_on.required' => 'Tanggal pendaftaran wajib diisi.',
            'enrolled_on.date_format' => 'Tanggal pendaftaran tidak valid.',
            'enrolled_on.before_or_equal' => 'Tanggal pendaftaran tidak boleh melebihi hari ini.',
            'admin_notes.max' => 'Catatan admin maksimal 2000 karakter.',
        ];
    }

    /**
     * Pendaftaran yang sedang diedit, atau null saat menambah pendaftaran baru.
     */
    public function enrollment(): ?Enrollment
    {
        $enrollment = $this->route('enrollment');

        return $enrollment instanceof Enrollment ? $enrollment : null;
    }
}
