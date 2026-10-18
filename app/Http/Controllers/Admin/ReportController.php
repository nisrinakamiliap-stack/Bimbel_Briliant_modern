<?php

namespace App\Http\Controllers\Admin;

use App\AttendanceStatus;
use App\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Payment;
use App\Models\Program;
use App\Models\StudentAttendance;
use App\Models\Submission;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\PaymentMethod;
use App\PaymentStatus;
use App\TeacherAttendanceStatus;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Laporan admin yang seluruh angkanya dihitung dari database sesuai filter periode.
 *
 * Setiap laporan dapat diekspor ke CSV (`?export=csv`) dengan filter yang sama.
 */
class ReportController extends Controller
{
    /**
     * Laporan murid dan pendaftaran.
     */
    public function students(Request $request): View|StreamedResponse
    {
        $filters = $this->filters($request, ['program' => ['nullable', 'integer', Rule::exists('programs', 'id')]]);
        [$from, $to] = $this->period($filters);

        $enrollments = Enrollment::query()
            ->with(['student', 'program', 'classroom'])
            ->whereBetween('enrolled_on', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->when($filters['program'] ?? null, fn (Builder $query, $id) => $query->where('program_id', (int) $id))
            ->orderBy('enrolled_on')
            ->get();

        if ($request->query('export') === 'csv') {
            return $this->csv('laporan-pendaftaran', ['Tanggal Daftar', 'Nama Murid', 'Email', 'Program', 'Kelas', 'Status', 'Total Tagihan'],
                $enrollments->map(fn (Enrollment $enrollment) => [
                    $enrollment->enrolled_on->format('d/m/Y'),
                    $enrollment->student->name,
                    $enrollment->student->email,
                    $enrollment->program->name,
                    $enrollment->classroom?->name ?? '-',
                    $enrollment->status->label(),
                    Payment::rupiah($enrollment->bill_amount),
                ]));
        }

        $activeEnrollments = Enrollment::query()->active()->with(['program', 'classroom'])->get();

        return view('admin.reports.students', [
            'filters' => $filters,
            'from' => $from,
            'to' => $to,
            'programs' => Program::query()->orderBy('name')->get(['id', 'name']),
            'totalStudents' => User::query()->students()->count(),
            'activeStudents' => Enrollment::query()->where('status', EnrollmentStatus::Approved->value)->distinct()->count('student_id'),
            'statusCounts' => collect(EnrollmentStatus::cases())->mapWithKeys(fn (EnrollmentStatus $status) => [$status->label() => $enrollments->where('status', $status)->count()]),
            'monthly' => $this->monthlyCounts($enrollments, fn (Enrollment $enrollment) => $enrollment->enrolled_on),
            'byProgram' => $activeEnrollments->groupBy(fn (Enrollment $enrollment) => $enrollment->program->name)->map->count()->sortDesc(),
            'byClass' => $activeEnrollments->whereNotNull('classroom_id')->groupBy(fn (Enrollment $enrollment) => $enrollment->classroom->name)->map->count()->sortDesc(),
            'enrollmentCount' => $enrollments->count(),
        ]);
    }

    /**
     * Laporan pembayaran. Hanya transaksi berstatus Berhasil yang dihitung sebagai pendapatan diterima.
     */
    public function payments(Request $request): View|StreamedResponse
    {
        $filters = $this->filters($request, ['metode' => ['nullable', Rule::enum(PaymentMethod::class)]]);
        [$from, $to] = $this->period($filters);

        $payments = Payment::query()
            ->with(['enrollment.student', 'enrollment.program'])
            ->whereBetween('paid_on', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->when($filters['metode'] ?? null, fn (Builder $query, string $method) => $query->where('method', $method))
            ->orderBy('paid_on')
            ->get();

        if ($request->query('export') === 'csv') {
            return $this->csv('laporan-pembayaran', ['Tanggal', 'No. Transaksi', 'Nama Murid', 'Program', 'Metode', 'Nominal', 'Status'],
                $payments->map(fn (Payment $payment) => [
                    $payment->paid_on->format('d/m/Y'),
                    $payment->code,
                    $payment->enrollment->student->name,
                    $payment->enrollment->program->name,
                    $payment->method->label(),
                    Payment::rupiah($payment->amount),
                    $payment->status->label(),
                ]));
        }

        $sumFor = fn (PaymentStatus $status): float => $payments->where('status', $status)->sum(fn (Payment $payment) => (float) $payment->amount);

        $outstanding = Enrollment::query()
            ->active()
            ->withSum(['payments as paid_total' => fn (Builder $query) => $query->where('status', PaymentStatus::Paid->value)], 'amount')
            ->get(['id', 'bill_amount'])
            ->map(fn (Enrollment $enrollment) => $enrollment->remainingCents())
            ->filter(fn (int $cents) => $cents > 0);

        $paidPayments = $payments->where('status', PaymentStatus::Paid);

        return view('admin.reports.payments', [
            'filters' => $filters,
            'from' => $from,
            'to' => $to,
            'methods' => PaymentMethod::cases(),
            'summary' => [
                'paid' => ['count' => $paidPayments->count(), 'total' => $sumFor(PaymentStatus::Paid)],
                'waiting' => ['count' => $payments->where('status', PaymentStatus::WaitingVerification)->count(), 'total' => $sumFor(PaymentStatus::WaitingVerification)],
                'rejected' => ['count' => $payments->where('status', PaymentStatus::Rejected)->count(), 'total' => $sumFor(PaymentStatus::Rejected)],
                'outstanding' => ['count' => $outstanding->count(), 'total' => $outstanding->sum() / 100],
            ],
            'monthlyPaid' => $paidPayments
                ->groupBy(fn (Payment $payment) => $payment->paid_on->format('Y-m'))
                ->sortKeys()
                ->map(fn (Collection $group, string $month) => [
                    'label' => CarbonImmutable::createFromFormat('Y-m', $month)->translatedFormat('F Y'),
                    'count' => $group->count(),
                    'total' => $group->sum(fn (Payment $payment) => (float) $payment->amount),
                ])
                ->values(),
            'byMethod' => collect(PaymentMethod::cases())->mapWithKeys(fn (PaymentMethod $method) => [
                $method->label() => $paidPayments->where('method', $method)->sum(fn (Payment $payment) => (float) $payment->amount),
            ]),
        ]);
    }

    /**
     * Laporan absensi murid dan guru. Hanya sesi yang sudah tercatat yang dihitung.
     */
    public function attendance(Request $request): View|StreamedResponse
    {
        $filters = $this->filters($request, [
            'kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')],
            'murid' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'guru' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);
        [$from, $to] = $this->period($filters);
        $range = [$from->format('Y-m-d'), $to->format('Y-m-d')];

        $studentRecords = StudentAttendance::query()
            ->with(['student', 'schedule.classroom'])
            ->whereBetween('session_date', $range)
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->whereHas('schedule', fn (Builder $query) => $query->where('classroom_id', (int) $id)))
            ->when($filters['murid'] ?? null, fn (Builder $query, $id) => $query->where('student_id', (int) $id))
            ->get();

        $teacherRecords = TeacherAttendance::query()
            ->with(['teacher', 'schedule.classroom'])
            ->whereBetween('session_date', $range)
            ->when($filters['kelas'] ?? null, fn (Builder $query, $id) => $query->whereHas('schedule', fn (Builder $query) => $query->where('classroom_id', (int) $id)))
            ->when($filters['guru'] ?? null, fn (Builder $query, $id) => $query->where('teacher_id', (int) $id))
            ->get();

        $studentRecap = $studentRecords
            ->groupBy('student_id')
            ->map(fn (Collection $group) => [
                'name' => $group->first()->student->name,
                'counts' => collect(AttendanceStatus::cases())->mapWithKeys(fn (AttendanceStatus $status) => [$status->value => $group->where('status', $status)->count()]),
                'total' => $group->count(),
            ])
            ->sortBy('name')
            ->values();

        if ($request->query('export') === 'csv') {
            return $this->csv('laporan-absensi-murid', ['Nama Murid', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Total Sesi', '% Hadir'],
                $studentRecap->map(fn (array $row) => [
                    $row['name'],
                    $row['counts']['present'],
                    $row['counts']['excused'],
                    $row['counts']['sick'],
                    $row['counts']['absent'],
                    $row['total'],
                    $row['total'] > 0 ? round($row['counts']['present'] / $row['total'] * 100).'%' : '0%',
                ]));
        }

        return view('admin.reports.attendance', [
            'filters' => $filters,
            'from' => $from,
            'to' => $to,
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name']),
            'students' => User::query()->students()->orderBy('name')->get(['id', 'name']),
            'teachers' => User::query()->teachers()->orderBy('name')->get(['id', 'name']),
            'studentCounts' => collect(AttendanceStatus::cases())->mapWithKeys(fn (AttendanceStatus $status) => [$status->label() => $studentRecords->where('status', $status)->count()]),
            'teacherCounts' => collect(TeacherAttendanceStatus::cases())->mapWithKeys(fn (TeacherAttendanceStatus $status) => [$status->label() => $teacherRecords->where('status', $status)->count()]),
            'studentRecap' => $studentRecap,
            'teacherRecap' => $teacherRecords->groupBy('teacher_id')->map(fn (Collection $group) => [
                'name' => $group->first()->teacher->name,
                'sessions' => $group->count(),
                'late' => $group->where('status', TeacherAttendanceStatus::Late)->count(),
                'minutes' => $group->sum(fn (TeacherAttendance $record) => $record->durationMinutes() ?? 0),
            ])->sortBy('name')->values(),
        ]);
    }

    /**
     * Laporan pembelajaran: materi, tugas, pengumpulan, tugas yang belum dikumpulkan, dan rekap nilai.
     */
    public function learning(Request $request): View|StreamedResponse
    {
        $filters = $this->filters($request, ['kelas' => ['nullable', 'integer', Rule::exists('classes', 'id')]]);
        [$from, $to] = $this->period($filters);
        $classroomId = isset($filters['kelas']) ? (int) $filters['kelas'] : null;

        $assignments = Assignment::query()
            ->published()
            ->whereBetween('due_at', [$from->startOfDay(), $to->endOfDay()])
            ->when($classroomId, fn (Builder $query, int $id) => $query->where('classroom_id', $id))
            ->with(['classroom', 'submissions.grade'])
            ->orderBy('due_at')
            ->get();

        $participantCounts = Enrollment::query()
            ->where('status', EnrollmentStatus::Approved->value)
            ->whereIn('classroom_id', $assignments->pluck('classroom_id')->unique())
            ->selectRaw('classroom_id, count(distinct student_id) as total')
            ->groupBy('classroom_id')
            ->pluck('total', 'classroom_id');

        $rows = $assignments->map(function (Assignment $assignment) use ($participantCounts) {
            $participants = (int) ($participantCounts[$assignment->classroom_id] ?? 0);
            $submitted = $assignment->submissions->count();
            $grades = $assignment->submissions->pluck('grade')->filter();
            $isPastDue = $assignment->isPastDue(now());

            return [
                'title' => $assignment->title,
                'classroom' => $assignment->classroom->name,
                'due_at' => $assignment->due_at,
                'participants' => $participants,
                'submitted' => $submitted,
                'missing' => $isPastDue ? max(0, $participants - $submitted) : null,
                'late' => $assignment->submissions->where('is_late', true)->count(),
                'graded' => $grades->count(),
                'average' => $grades->isEmpty() ? null : round($grades->avg(fn ($grade) => (float) $grade->score), 1),
                'max_score' => $assignment->max_score,
            ];
        });

        if ($request->query('export') === 'csv') {
            return $this->csv('laporan-pembelajaran', ['Tugas', 'Kelas', 'Tenggat', 'Peserta', 'Mengumpulkan', 'Belum Mengumpulkan', 'Terlambat', 'Dinilai', 'Rata-rata Nilai', 'Skala'],
                $rows->map(fn (array $row) => [
                    $row['title'],
                    $row['classroom'],
                    $row['due_at']->format('d/m/Y H:i'),
                    $row['participants'],
                    $row['submitted'],
                    $row['missing'] ?? 'Belum lewat tenggat',
                    $row['late'],
                    $row['graded'],
                    $row['average'] === null ? '-' : str_replace('.', ',', (string) $row['average']),
                    $row['max_score'],
                ]));
        }

        return view('admin.reports.learning', [
            'filters' => $filters,
            'from' => $from,
            'to' => $to,
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name']),
            'rows' => $rows,
            'totals' => [
                'materials' => Material::query()->published()->when($classroomId, fn (Builder $query, int $id) => $query->where('classroom_id', $id))->count(),
                'assignments' => $assignments->count(),
                'submissions' => Submission::query()
                    ->whereBetween('submitted_at', [$from->startOfDay(), $to->endOfDay()])
                    ->when($classroomId, fn (Builder $query, int $id) => $query->whereHas('assignment', fn (Builder $query) => $query->where('classroom_id', $id)))
                    ->count(),
                'missing' => $rows->sum(fn (array $row) => $row['missing'] ?? 0),
                'graded' => $rows->sum('graded'),
                'ungraded' => $rows->sum('submitted') - $rows->sum('graded'),
            ],
        ]);
    }

    /**
     * Validasi filter periode (default: awal bulan berjalan sampai hari ini) dan filter tambahan.
     *
     * @param  array<string, array<int, mixed>>  $extraRules
     * @return array<string, mixed>
     */
    private function filters(Request $request, array $extraRules = []): array
    {
        return $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
            'export' => ['nullable', Rule::in(['csv'])],
            ...$extraRules,
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir periode tidak boleh lebih awal daripada tanggal awal.',
        ]);
    }

    /**
     * Periode laporan.
     *
     * @param  array<string, mixed>  $filters
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function period(array $filters): array
    {
        $from = isset($filters['dari']) ? CarbonImmutable::parse($filters['dari']) : CarbonImmutable::now()->startOfMonth();
        $to = isset($filters['sampai']) ? CarbonImmutable::parse($filters['sampai']) : CarbonImmutable::now();

        return [$from->startOfDay(), $to->startOfDay()];
    }

    /**
     * Jumlah data per bulan.
     *
     * @param  Collection<int, mixed>  $items
     * @return Collection<int, array{label: string, count: int}>
     */
    private function monthlyCounts(Collection $items, callable $dateOf): Collection
    {
        return $items
            ->groupBy(fn ($item) => $dateOf($item)->format('Y-m'))
            ->sortKeys()
            ->map(fn (Collection $group, string $month) => [
                'label' => CarbonImmutable::createFromFormat('Y-m', $month)->translatedFormat('F Y'),
                'count' => $group->count(),
            ])
            ->values();
    }

    /**
     * Respons unduhan CSV (UTF-8 dengan BOM agar terbaca benar di Excel, pemisah titik koma).
     *
     * @param  array<int, string>  $headers
     * @param  Collection<int, array<int, mixed>>  $rows
     */
    private function csv(string $name, array $headers, Collection $rows): StreamedResponse
    {
        $filename = $name.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, array_map(fn ($value) => is_string($value) && preg_match('/^[=+\-@].+/', $value) ? "'".$value : $value, $row), ';');
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
