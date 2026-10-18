<?php

namespace App\Http\Requests\Admin;

use App\Models\Classroom;
use App\Models\Schedule;
use App\UserRole;
use App\Weekday;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi form tambah dan edit jadwal belajar, termasuk pemeriksaan bentrok jadwal.
 */
class ScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Menormalkan jam ("08:00:00" menjadi "08:00") dan tanggal kosong menjadi null.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'start_time' => $this->normalizeTimeInput($this->input('start_time')),
            'end_time' => $this->normalizeTimeInput($this->input('end_time')),
            'starts_on' => blank($this->input('starts_on')) ? null : $this->input('starts_on'),
            'ends_on' => blank($this->input('ends_on')) ? null : $this->input('ends_on'),
            'room' => blank($this->input('room')) ? null : trim((string) $this->input('room')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Kelas baru hanya boleh dipilih jika masih aktif; kelas yang sudah terpasang pada jadwal
     * yang sedang diedit tetap diterima walaupun kini nonaktif.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $classroomExists = Rule::exists('classes', 'id');
        if ((string) $this->input('classroom_id') !== (string) $this->schedule()?->classroom_id) {
            $classroomExists->where('is_active', true);
        }

        return [
            'classroom_id' => ['required', 'integer', $classroomExists],
            'day_of_week' => ['required', Rule::enum(Weekday::class)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'starts_on' => ['nullable', 'date_format:Y-m-d'],
            'ends_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'room' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Pemeriksaan bentrok jadwal setelah validasi dasar lolos.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->boolean('is_active')) {
                    return;
                }

                $conflict = Schedule::findConflict(
                    Classroom::findOrFail($this->integer('classroom_id')),
                    Weekday::from($this->integer('day_of_week')),
                    (string) $this->input('start_time'),
                    (string) $this->input('end_time'),
                    $this->input('starts_on'),
                    $this->input('ends_on'),
                    $this->schedule()?->id,
                );

                if ($conflict !== null) {
                    $validator->errors()->add('start_time', Schedule::conflictMessage($conflict));
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
            'classroom_id.required' => 'Silakan pilih kelas.',
            'classroom_id.integer' => 'Kelas yang dipilih tidak valid.',
            'classroom_id.exists' => 'Kelas tidak ditemukan atau sudah tidak aktif.',
            'day_of_week.required' => 'Silakan pilih hari pelaksanaan.',
            'day_of_week.enum' => 'Hari yang dipilih tidak valid.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'start_time.date_format' => 'Format jam mulai tidak valid. Gunakan format 24 jam, contoh: 08:00.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.date_format' => 'Format jam selesai tidak valid. Gunakan format 24 jam, contoh: 10:00.',
            'end_time.after' => 'Jam selesai harus lebih akhir daripada jam mulai.',
            'starts_on.date_format' => 'Tanggal mulai berlaku tidak valid.',
            'ends_on.date_format' => 'Tanggal berakhir tidak valid.',
            'ends_on.after_or_equal' => 'Tanggal berakhir tidak boleh lebih awal daripada tanggal mulai berlaku.',
            'room.max' => 'Nama ruangan maksimal 100 karakter.',
            'is_active.required' => 'Silakan pilih status jadwal.',
            'is_active.boolean' => 'Status jadwal tidak valid.',
        ];
    }

    /**
     * Jadwal yang sedang diedit, atau null saat menambah jadwal baru.
     */
    private function schedule(): ?Schedule
    {
        $schedule = $this->route('schedule');

        return $schedule instanceof Schedule ? $schedule : null;
    }

    /**
     * Memotong detik dari input jam, misalnya "08:00:00" menjadi "08:00".
     */
    private function normalizeTimeInput(mixed $time): mixed
    {
        if (is_string($time) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $time) === 1) {
            return substr($time, 0, 5);
        }

        return $time;
    }
}
