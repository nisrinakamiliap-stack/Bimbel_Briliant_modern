<?php

namespace App\Http\Requests\Student;

use App\Models\Assignment;
use App\Policies\AssignmentPolicy;
use Closure;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

/**
 * Validasi pengumpulan tugas oleh murid. Hak mengumpulkan (kelas, tenggat, status nilai)
 * diperiksa oleh {@see AssignmentPolicy::submit()}.
 */
class SubmissionRequest extends FormRequest
{
    /**
     * Jenis berkas jawaban yang diizinkan.
     */
    public const ALLOWED_MIMES = 'pdf,doc,docx,jpg,jpeg,png,zip';

    /**
     * Ukuran maksimal berkas jawaban dalam kilobyte (10 MB).
     */
    public const MAX_FILE_KILOBYTES = 10240;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::forUser($this->user())->inspect('submit', $this->assignment());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answer_text' => ['nullable', 'string', 'max:20000'],
            'file' => ['nullable', 'file', 'mimes:'.self::ALLOWED_MIMES, 'max:'.self::MAX_FILE_KILOBYTES],
        ];
    }

    /**
     * Jawaban teks atau berkas wajib diisi salah satu (berkas lama dianggap tetap ada saat memperbarui).
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasExistingFile = $this->assignment()->submissions()
                    ->where('student_id', $this->user()->id)
                    ->whereNotNull('file_path')
                    ->exists();

                if (blank($this->input('answer_text')) && ! $this->hasFile('file') && ! $hasExistingFile) {
                    $validator->errors()->add('answer_text', 'Isi jawaban teks atau unggah berkas jawaban.');
                }
            },
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
            'answer_text.max' => 'Jawaban terlalu panjang.',
            'file.file' => 'Berkas jawaban tidak valid.',
            'file.mimes' => 'Berkas jawaban harus berupa PDF, Word, gambar JPG/PNG, atau ZIP.',
            'file.max' => 'Ukuran berkas jawaban maksimal 10 MB.',
            'file.uploaded' => 'Berkas gagal diunggah. Pastikan ukurannya tidak lebih dari 10 MB.',
        ];
    }

    /**
     * Tugas yang dikumpulkan.
     */
    public function assignment(): Assignment
    {
        /** @var Assignment $assignment */
        $assignment = $this->route('assignment');

        return $assignment->loadMissing('classroom');
    }
}
