<?php

namespace App\Http\Requests\Admin\Learning;

use App\LearningContentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi form tambah dan edit tugas.
 *
 * Tenggat harus di masa depan saat tugas dibuat atau tenggatnya diubah. Tugas yang sudah memiliki
 * pengumpulan tidak dapat dipindah kelas, dikembalikan menjadi draf, atau diturunkan skala nilainya
 * di bawah nilai yang sudah diberikan.
 */
class AssignmentRequest extends FormRequest
{
    /**
     * Format input tanggal-waktu dari elemen datetime-local.
     */
    public const DUE_AT_FORMAT = 'Y-m-d\TH:i';

    /**
     * Hanya pengguna yang berhak mengelola tugas pada kelas tujuan yang dapat menyimpan tugas.
     */
    public function authorize(): bool
    {
        $classroom = Classroom::find($this->integer('classroom_id'));

        if ($classroom === null) {
            return $this->user() !== null;
        }

        return $this->user()?->can('create', [Assignment::class, $classroom]) ?? false;
    }

    /**
     * Menormalkan checkbox dan materi kosong.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'material_id' => blank($this->input('material_id')) ? null : $this->input('material_id'),
            'allow_late_submission' => $this->boolean('allow_late_submission'),
            'allow_resubmission' => $this->boolean('allow_resubmission'),
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
            'classroom_id' => ['required', 'integer', Rule::exists('classes', 'id')],
            'material_id' => ['nullable', 'integer', Rule::exists('materials', 'id')->where('classroom_id', $this->integer('classroom_id'))],
            'title' => ['required', 'string', 'max:200'],
            'instructions' => ['required', 'string', 'max:20000'],
            'due_at' => ['required', 'date_format:'.self::DUE_AT_FORMAT],
            'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
            'allow_late_submission' => ['boolean'],
            'allow_resubmission' => ['boolean'],
            'attachment' => ['nullable', 'file', 'mimes:'.MaterialRequest::ALLOWED_MIMES, 'max:'.MaterialRequest::MAX_FILE_KILOBYTES],
            'remove_attachment' => ['nullable', 'boolean'],
            'status' => ['required', Rule::enum(LearningContentStatus::class)],
        ];
    }

    /**
     * Aturan yang bergantung pada data tugas yang sudah ada.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['due_at', 'classroom_id', 'status', 'max_score'])) {
                    return;
                }

                $assignment = $this->assignment();
                $dueAt = CarbonImmutable::createFromFormat(self::DUE_AT_FORMAT, (string) $this->input('due_at'));
                $isDueAtChanged = $assignment === null || $assignment->due_at->format(self::DUE_AT_FORMAT) !== $this->input('due_at');

                if ($isDueAtChanged && $dueAt->lessThanOrEqualTo(now())) {
                    $validator->errors()->add('due_at', 'Tenggat harus lebih dari waktu saat ini.');
                }

                if ($assignment === null || ! $assignment->hasSubmissions()) {
                    return;
                }

                if ((int) $this->input('classroom_id') !== $assignment->classroom_id) {
                    $validator->errors()->add('classroom_id', 'Kelas tidak dapat diganti karena tugas sudah memiliki pengumpulan.');
                }

                if ($this->input('status') === LearningContentStatus::Draft->value) {
                    $validator->errors()->add('status', 'Tugas yang sudah memiliki pengumpulan tidak dapat dikembalikan menjadi draf.');
                }

                $highestScore = (float) $assignment->submissions()->join('assignment_grades', 'assignment_grades.submission_id', '=', 'submissions.id')->max('assignment_grades.score');
                if ($this->integer('max_score') < $highestScore) {
                    $validator->errors()->add('max_score', 'Skala nilai tidak boleh lebih kecil dari nilai tertinggi yang sudah diberikan ('.rtrim(rtrim(number_format($highestScore, 2, ',', ''), '0'), ',').').');
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
            'classroom_id.required' => 'Silakan pilih kelas tujuan.',
            'classroom_id.exists' => 'Kelas tidak ditemukan.',
            'material_id.exists' => 'Materi terkait harus berasal dari kelas yang sama.',
            'title.required' => 'Judul tugas wajib diisi.',
            'title.max' => 'Judul tugas maksimal 200 karakter.',
            'instructions.required' => 'Instruksi tugas wajib diisi.',
            'instructions.max' => 'Instruksi tugas terlalu panjang.',
            'due_at.required' => 'Tenggat wajib diisi.',
            'due_at.date_format' => 'Format tenggat tidak valid.',
            'max_score.required' => 'Skala nilai maksimal wajib diisi.',
            'max_score.integer' => 'Skala nilai harus berupa bilangan bulat, contoh: 100.',
            'max_score.min' => 'Skala nilai minimal 1.',
            'max_score.max' => 'Skala nilai maksimal 1000.',
            'attachment.mimes' => 'Lampiran harus berupa PDF, Word, PowerPoint, Excel, gambar JPG/PNG, atau ZIP.',
            'attachment.max' => 'Ukuran lampiran maksimal 10 MB.',
            'attachment.uploaded' => 'Lampiran gagal diunggah. Pastikan ukurannya tidak lebih dari 10 MB.',
            'status.required' => 'Silakan pilih status tugas.',
            'status.enum' => 'Status tugas tidak valid.',
        ];
    }

    /**
     * Tugas yang sedang diedit, atau null saat membuat tugas baru.
     */
    private function assignment(): ?Assignment
    {
        $assignment = $this->route('assignment');

        return $assignment instanceof Assignment ? $assignment : null;
    }
}
