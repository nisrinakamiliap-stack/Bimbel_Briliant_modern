<?php

namespace App\Http\Controllers\Teacher;

use App\HonorariumStatus;
use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\TeacherHonorarium;
use App\Models\TeacherHonorariumPayment;
use App\TeacherAttendanceStatus;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Menampilkan ringkasan, rincian, dan riwayat honorarium milik guru yang login.
 *
 * Guru hanya memiliki hak akses baca untuk data honorarium dan riwayat pembayaran miliknya.
 * Penghitungan honor hanya menggunakan tarif dan penetapan resmi dari lembaga/Admin.
 */
class HonorariumController extends Controller
{
    /**
     * Jumlah baris riwayat per halaman.
     */
    private const PER_PAGE = 12;

    /**
     * Halaman utama Honorarium Saya: ringkasan periode terpilih, rincian, dan riwayat transaksi.
     */
    public function index(Request $request): View
    {
        $teacher = $request->user();

        // Periode yang dipilih oleh pengguna (default ke bulan & tahun berjalan)
        $selectedMonth = $request->integer('bulan') ?: (int) now()->format('n');
        $selectedYear  = $request->integer('tahun') ?: (int) now()->format('Y');

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int) now()->format('n');
        }
        if ($selectedYear < 2020 || $selectedYear > 2099) {
            $selectedYear = (int) now()->format('Y');
        }

        // Cari honorarium untuk guru pada periode yang dipilih
        $honorarium = TeacherHonorarium::query()
            ->where('teacher_id', $teacher->id)
            ->where('period_year', $selectedYear)
            ->where('period_month', $selectedMonth)
            ->with(['payments.creator', 'creator', 'approver', 'payer'])
            ->first();

        // Hitung sesi mengajar riil yang memenuhi syarat (Hadir atau Terlambat) dari data absensi
        // Menggunakan unique constraint (schedule_id, session_date) untuk menjamin tidak ada sesi yang dihitung ganda
        $verifiedAttendanceCount = TeacherAttendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereYear('session_date', $selectedYear)
            ->whereMonth('session_date', $selectedMonth)
            ->whereIn('status', [TeacherAttendanceStatus::Present->value, TeacherAttendanceStatus::Late->value])
            ->count();

        // Data ringkasan periode terpilih
        $isConfigured = $honorarium !== null;

        $periodData = [
            'month'                     => $selectedMonth,
            'year'                      => $selectedYear,
            'label'                     => CarbonImmutable::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y'),
            'is_configured'             => $isConfigured,
            'session_count'             => $isConfigured ? (int) $honorarium->session_count : $verifiedAttendanceCount,
            'verified_attendance_count' => $verifiedAttendanceCount,
            'rate_per_session'          => $isConfigured ? (float) $honorarium->rate_per_session : null,
            'base_amount'               => $isConfigured ? (float) $honorarium->base_amount : null,
            'adjustments'               => $isConfigured ? (float) $honorarium->adjustments : 0.0,
            'deductions'                => $isConfigured ? (float) $honorarium->deductions : 0.0,
            'total_amount'              => $isConfigured ? (float) $honorarium->amount : null,
            'paid_amount'               => $isConfigured ? (float) $honorarium->paid_amount : 0.0,
            'remaining_amount'          => $isConfigured ? $honorarium->remainingAmount() : null,
            'status'                    => $honorarium?->status,
            'notes'                     => $honorarium?->notes,
            'payments'                  => $honorarium ? $honorarium->payments : collect(),
            'honorarium'                => $honorarium,
        ];

        // Riwayat honorarium per periode milik guru
        $history = TeacherHonorarium::query()
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('teacher.honorariums.index', [
            'periodData' => $periodData,
            'history'    => $history,
        ]);
    }

    /**
     * Detail catatan honorarium milik guru yang sedang login.
     */
    public function show(Request $request, TeacherHonorarium $honorarium): View
    {
        abort_unless($honorarium->teacher_id === $request->user()->id, 403);

        $honorarium->load(['creator', 'approver', 'payer', 'payments.creator']);

        return view('teacher.honorariums.show', [
            'honorarium' => $honorarium,
        ]);
    }

    /**
     * Unduh/tampilkan berkas bukti pembayaran honorarium milik guru yang sedang login.
     */
    public function proof(Request $request, TeacherHonorariumPayment $payment): StreamedResponse
    {
        $payment->loadMissing('honorarium');

        abort_unless($payment->honorarium->teacher_id === $request->user()->id, 403);
        abort_unless(
            $payment->hasProof() && Storage::disk(TeacherHonorariumPayment::PROOF_DISK)->exists($payment->proof_path),
            404
        );

        return Storage::disk(TeacherHonorariumPayment::PROOF_DISK)->response(
            $payment->proof_path,
            $payment->proof_original_name ?? basename($payment->proof_path),
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'],
        );
    }
}
