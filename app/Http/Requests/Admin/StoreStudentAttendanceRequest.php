<?php

namespace App\Http\Requests\Admin;

use App\AttendanceStatus;
use App\Models\Schedule;
use App\UserRole;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi pencatatan absensi murid untuk satu sesi (jadwal + tanggal).
 *
 * `attendances` berisi status per pendaftaran: `attendances[{enrollment_id}][status]`.
 * Kecocokan pendaftaran dengan daftar peserta kelas diperiksa ulang di controller.
 */
class StoreStudentAttendanceRequest extends FormRequest
{
    /**
     * Admin dapat mencatat absensi semua kelas; guru hanya untuk jadwal kelas yang diampunya.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        if ($user->hasRole(UserRole::Admin)) {
            return true;
        }

        $schedule = Schedule::with('classroom')->find($this->integer('schedule_id'));

        return $user->hasRole(UserRole::Teacher)
            && $schedule !== null
            && $schedule->classroom->teacher_id === $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'schedule_id' => ['required', 'integer', Rule::exists('schedules', 'id')],
            'session_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.status' => ['required', Rule::enum(AttendanceStatus::class)],
            'attendances.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Memastikan jadwal benar-benar berlangsung pada tanggal tersebut dan sesi sudah dimulai.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['schedule_id', 'session_date'])) {
                    return;
                }

                $schedule = Schedule::with('classroom')->findOrFail($this->integer('schedule_id'));
                $date = CarbonImmutable::parse((string) $this->input('session_date'));

                if (! $schedule->classroom->is_active || ! $schedule->occursOn($date)) {
                    $validator->errors()->add('schedule_id', 'Jadwal ini tidak berlangsung pada '.$date->translatedFormat('l, d F Y').'.');

                    return;
                }

                if (now()->lessThan($schedule->sessionStartsAt($date))) {
                    $validator->errors()->add('schedule_id', 'Sesi belum dimulai. Absensi dapat dicatat mulai pukul '.$schedule->sessionStartsAt($date)->format('H:i').'.');
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
            'schedule_id.required' => 'Silakan pilih jadwal.',
            'schedule_id.exists' => 'Jadwal tidak ditemukan.',
            'session_date.required' => 'Tanggal sesi wajib diisi.',
            'session_date.date_format' => 'Tanggal sesi tidak valid.',
            'session_date.before_or_equal' => 'Absensi tidak dapat dicatat untuk tanggal yang akan datang.',
            'attendances.required' => 'Tidak ada status kehadiran yang dikirim.',
            'attendances.*.status.required' => 'Status kehadiran setiap murid wajib dipilih.',
            'attendances.*.status.enum' => 'Status kehadiran tidak valid.',
            'attendances.*.notes.max' => 'Keterangan maksimal 255 karakter.',
        ];
    }
}
