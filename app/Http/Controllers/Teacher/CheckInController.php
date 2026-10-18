<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\TeacherAttendance;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Endpoint check-in/check-out guru untuk Dashboard Guru.
 *
 * Waktu selalu diambil dari server (zona waktu aplikasi); tidak ada input waktu dari browser.
 * Seluruh aturan (jadwal milik guru, jendela waktu, duplikasi) diperiksa di {@see TeacherAttendance}.
 */
class CheckInController extends Controller
{
    /**
     * Check-in untuk sesi jadwal hari ini.
     */
    public function store(Request $request, Schedule $schedule): RedirectResponse
    {
        try {
            $attendance = DB::transaction(fn () => TeacherAttendance::checkIn($request->user(), $schedule, now()));
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['schedule' => 'Anda sudah check-in untuk sesi ini.']);
        }

        return back()->with('success', "Check-in berhasil pukul {$attendance->checked_in_at->format('H:i')} ({$attendance->status->label()}).");
    }

    /**
     * Check-out untuk sesi jadwal hari ini yang sudah di-check-in.
     */
    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $attendance = DB::transaction(fn () => TeacherAttendance::checkOut($request->user(), $schedule, now()));

        return back()->with('success', "Check-out berhasil pukul {$attendance->checked_out_at->format('H:i')}. Durasi mengajar: {$attendance->durationLabel()}.");
    }
}
