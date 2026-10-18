<?php

namespace App\Http\Requests;

use App\Models\Submission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi penilaian pengumpulan (dipakai admin dan guru). Rentang nilai mengikuti skala tugas.
 */
class GradeRequest extends FormRequest
{
    /**
     * Hanya admin atau guru pengampu kelas tugas yang dapat menilai.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('grade', $this->submission()) ?? false;
    }

    /**
     * Menerima nilai dengan koma desimal, misalnya "87,5".
     */
    protected function prepareForValidation(): void
    {
        $score = $this->input('score');

        $this->merge([
            'score' => is_string($score) ? str_replace(',', '.', trim($score)) : $score,
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
            'score' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.$this->submission()->assignment->max_score],
            'feedback' => ['nullable', 'string', 'max:2000'],
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
            'score.required' => 'Nilai wajib diisi.',
            'score.numeric' => 'Nilai harus berupa angka.',
            'score.decimal' => 'Nilai maksimal memiliki 2 angka di belakang koma.',
            'score.min' => 'Nilai tidak boleh kurang dari 0.',
            'score.max' => 'Nilai tidak boleh melebihi skala maksimal tugas (:max).',
            'feedback.max' => 'Umpan balik maksimal 2000 karakter.',
        ];
    }

    /**
     * Pengumpulan yang dinilai.
     */
    public function submission(): Submission
    {
        /** @var Submission $submission */
        $submission = $this->route('submission');

        return $submission->loadMissing('assignment.classroom');
    }
}
