<?php

namespace App\Http\Requests\Admin;

use App\LearningMode;
use App\Models\Classroom;
use App\UserRole;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi form tambah dan edit kelas oleh admin.
 *
 * Program dan guru baru hanya boleh dipilih jika masih aktif. Nilai yang sudah tersimpan
 * pada kelas yang sedang diedit tetap diterima walaupun program/guru tersebut kini nonaktif,
 * agar data lama tetap bisa diperbarui tanpa dipaksa berganti.
 */
class ClassroomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Menormalkan nama, kode kelas (huruf kapital), dan guru kosong menjadi null.
     */
    protected function prepareForValidation(): void
    {
        $code = strtoupper(trim((string) $this->input('code')));
        $teacherId = $this->input('teacher_id');

        $this->merge([
            'name' => preg_replace('/\s+/', ' ', trim((string) $this->input('name'))),
            'code' => $code === '' ? null : $code,
            'teacher_id' => $teacherId === '' ? null : $teacherId,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $classroom = $this->classroom();

        $programExists = Rule::exists('programs', 'id');
        if ((string) $this->input('program_id') !== (string) $classroom?->program_id) {
            $programExists->where('is_active', true);
        }

        $teacherExists = Rule::exists('users', 'id')->where('role', UserRole::Teacher->value);
        if ((string) $this->input('teacher_id') !== (string) $classroom?->teacher_id) {
            $teacherExists->withoutTrashed();
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:30', 'regex:/^[A-Z0-9\-]+$/', Rule::unique(Classroom::class, 'code')->ignore($classroom)],
            'program_id' => ['required', 'integer', $programExists],
            'teacher_id' => ['nullable', 'integer', $teacherExists],
            'capacity' => ['required', 'integer', 'min:'.max(1, $this->occupiedSeats()), 'max:500'],
            'learning_mode' => ['required', Rule::enum(LearningMode::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Saat guru kelas diganti, pastikan guru baru tidak bentrok dengan jadwal kelas lain yang diampunya.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $classroom = $this->classroom();
                $newTeacherId = $this->input('teacher_id');

                if ($validator->errors()->isNotEmpty() || $classroom === null || $newTeacherId === null
                    || (string) $newTeacherId === (string) $classroom->teacher_id) {
                    return;
                }

                $activeSchedules = $classroom->schedules()->where('is_active', true)->get();

                foreach ($activeSchedules as $schedule) {
                    $conflict = $schedule->conflictingScheduleForTeacher((int) $newTeacherId);

                    if ($conflict !== null) {
                        $validator->errors()->add(
                            'teacher_id',
                            "Guru ini sudah mengajar kelas {$conflict->classroom->name} pada {$conflict->summary()}, bentrok dengan jadwal kelas ini pada {$schedule->summary()}."
                        );

                        return;
                    }
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
            'name.required' => 'Nama kelas wajib diisi.',
            'name.max' => 'Nama kelas maksimal 150 karakter.',
            'code.max' => 'Kode kelas maksimal 30 karakter.',
            'code.regex' => 'Kode kelas hanya boleh berisi huruf, angka, dan tanda hubung (-), contoh: SMA-IPA-01.',
            'code.unique' => 'Kode kelas ini sudah digunakan oleh kelas lain.',
            'program_id.required' => 'Silakan pilih program bimbel.',
            'program_id.integer' => 'Program bimbel yang dipilih tidak valid.',
            'program_id.exists' => 'Program bimbel tidak ditemukan atau sudah tidak aktif.',
            'teacher_id.integer' => 'Guru pengajar yang dipilih tidak valid.',
            'teacher_id.exists' => 'Guru pengajar tidak ditemukan atau akunnya sudah dihapus.',
            'capacity.required' => 'Kapasitas kelas wajib diisi.',
            'capacity.integer' => 'Kapasitas harus berupa bilangan bulat, contoh: 20.',
            'capacity.min' => $this->occupiedSeats() > 1
                ? 'Kapasitas tidak boleh kurang dari jumlah murid aktif di kelas ini (:min murid).'
                : 'Kapasitas minimal 1 murid.',
            'capacity.max' => 'Kapasitas maksimal 500 murid.',
            'learning_mode.required' => 'Silakan pilih mode pembelajaran.',
            'learning_mode.enum' => 'Mode pembelajaran yang dipilih tidak valid.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
            'is_active.required' => 'Silakan pilih status kelas.',
            'is_active.boolean' => 'Status kelas tidak valid.',
        ];
    }

    /**
     * Jumlah murid berbeda yang menempati kursi kelas yang sedang diedit (pendaftaran aktif).
     *
     * Kapasitas tidak boleh diturunkan di bawah angka ini.
     */
    private function occupiedSeats(): int
    {
        return $this->classroom()?->enrollments()->active()->distinct()->count('student_id') ?? 0;
    }

    /**
     * Kelas yang sedang diedit, atau null saat menambah kelas baru.
     */
    private function classroom(): ?Classroom
    {
        $classroom = $this->route('classroom');

        return $classroom instanceof Classroom ? $classroom : null;
    }
}
