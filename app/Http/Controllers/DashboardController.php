<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\User;
use App\PaymentStatus;
use App\UserRole;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Jumlah pertemuan terdekat yang ditampilkan di dashboard admin.
     */
    private const UPCOMING_SCHEDULE_LIMIT = 5;

    /**
     * Jumlah pendaftaran terbaru yang ditampilkan di dashboard admin.
     */
    private const RECENT_ENROLLMENT_LIMIT = 5;

    /**
     * Jumlah pembayaran menunggu verifikasi yang ditampilkan di dashboard admin.
     */
    private const PENDING_PAYMENT_LIMIT = 5;

    /**
     * Mengarahkan pengguna ke dashboard sesuai role-nya.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->dashboardRoute());
    }

    /**
     * Dashboard admin berisi ringkasan statistik dan aktivitas terbaru.
     *
     * Seluruh angka dan daftar diambil dari database tanpa angka fiktif.
     */
    public function admin(): View
    {
        /** @var array{students: int, teachers: int, programs: int, active_programs: int, classes: int, active_classes: int} $stats */
        $stats = [
            'students' => User::where('role', UserRole::Student)->count(),
            'teachers' => User::where('role', UserRole::Teacher)->count(),
            'programs' => Program::count(),
            'active_programs' => Program::where('is_active', true)->count(),
            'classes' => Classroom::count(),
            'active_classes' => Classroom::where('is_active', true)->count(),
        ];

        /** @var Collection<int, array{student_name: string, program: string, registered_at: CarbonInterface, status: string, badge: string, detail_url: string}> $recentEnrollments */
        $recentEnrollments = Enrollment::query()
            ->with(['student', 'program'])
            ->latest()
            ->take(self::RECENT_ENROLLMENT_LIMIT)
            ->get()
            ->map(fn (Enrollment $enrollment): array => [
                'student_name' => $enrollment->student->name,
                'program' => $enrollment->program->name,
                'registered_at' => $enrollment->enrolled_on,
                'status' => $enrollment->status->label(),
                'badge' => $enrollment->status->badgeClass(),
                'detail_url' => route('admin.enrollments.show', $enrollment),
            ]);

        /** @var Collection<int, array{student_name: string, amount: float, paid_at: CarbonInterface, status: string, badge: string, detail_url: string}> $pendingPayments */
        $pendingPayments = Payment::query()
            ->where('status', PaymentStatus::WaitingVerification->value)
            ->with('enrollment.student')
            ->oldest('paid_on')
            ->take(self::PENDING_PAYMENT_LIMIT)
            ->get()
            ->map(fn (Payment $payment): array => [
                'student_name' => $payment->enrollment->student->name,
                'amount' => (float) $payment->amount,
                'paid_at' => $payment->paid_on,
                'status' => $payment->status->label(),
                'badge' => $payment->status->badgeClass(),
                'detail_url' => route('admin.payments.show', $payment),
            ]);

        $upcomingSchedules = $this->upcomingSchedules(now(), self::UPCOMING_SCHEDULE_LIMIT);

        return view('dashboard.admin', compact('stats', 'recentEnrollments', 'pendingPayments', 'upcomingSchedules'));
    }

    /**
     * Pertemuan terdekat dari jadwal aktif milik kelas aktif, diurutkan dari yang paling dekat.
     *
     * @return Collection<int, array{title: string, program: string, teacher: string, starts_at: CarbonInterface}>
     */
    private function upcomingSchedules(CarbonInterface $from, int $limit): Collection
    {
        return Schedule::query()
            ->where('is_active', true)
            ->whereHas('classroom', fn ($query) => $query->where('is_active', true))
            ->with(['classroom.program', 'classroom.teacher'])
            ->get()
            ->map(fn (Schedule $schedule): ?array => ($startsAt = $schedule->nextOccurrence($from)) === null ? null : [
                'title' => $schedule->classroom->name.($schedule->room ? ' · '.$schedule->room : ''),
                'program' => $schedule->classroom->program->name,
                'teacher' => $schedule->classroom->teacher?->name ?? 'Belum ditentukan',
                'starts_at' => $startsAt,
            ])
            ->filter()
            ->sortBy('starts_at')
            ->take($limit)
            ->values();
    }

    /**
     * Dashboard guru/tentor (placeholder).
     */
    public function teacher(): View
    {
        return view('dashboard.teacher');
    }

    /**
     * Dashboard murid (placeholder).
     */
    public function student(): View
    {
        return view('dashboard.student');
    }
}
