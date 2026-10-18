<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProcessPaymentRequest;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\PaymentMethod;
use App\PaymentStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Jumlah transaksi per halaman.
     */
    private const PER_PAGE = 10;

    /**
     * Disk penyimpanan privat untuk bukti pembayaran (tidak dapat diakses langsung dari web).
     */
    private const PROOF_DISK = 'local';

    /**
     * Menampilkan ringkasan dan daftar transaksi dengan pencarian, filter, dan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(PaymentStatus::class)],
            'metode' => ['nullable', Rule::enum(PaymentMethod::class)],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
            'pendaftaran' => ['nullable', 'integer', Rule::exists('enrollments', 'id')],
            'murid' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir periode tidak boleh lebih awal daripada tanggal awal.',
        ]);

        $search = trim($filters['q'] ?? '');
        $status = isset($filters['status']) ? PaymentStatus::from($filters['status']) : null;
        $method = isset($filters['metode']) ? PaymentMethod::from($filters['metode']) : null;
        $dateFrom = $filters['dari'] ?? null;
        $dateTo = $filters['sampai'] ?? null;
        $enrollmentId = isset($filters['pendaftaran']) ? (int) $filters['pendaftaran'] : null;
        $studentId = isset($filters['murid']) ? (int) $filters['murid'] : null;

        $payments = Payment::query()
            ->with(['enrollment.student', 'enrollment.program'])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhereHas('enrollment.student', fn (Builder $query) => $query->withTrashed()->where(
                        fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")
                    ));
            }))
            ->when($status, fn (Builder $query, PaymentStatus $status) => $query->where('status', $status->value))
            ->when($method, fn (Builder $query, PaymentMethod $method) => $query->where('method', $method->value))
            ->when($dateFrom, fn (Builder $query, string $date) => $query->where('paid_on', '>=', $date))
            ->when($dateTo, fn (Builder $query, string $date) => $query->where('paid_on', '<=', $date))
            ->when($enrollmentId, fn (Builder $query, int $id) => $query->where('enrollment_id', $id))
            ->when($studentId, fn (Builder $query, int $id) => $query->whereHas('enrollment', fn (Builder $query) => $query->where('student_id', $id)))
            ->orderByDesc('paid_on')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'summary' => $this->summary(),
            'search' => $search,
            'status' => $status,
            'method' => $method,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'enrollmentFilter' => $enrollmentId ? Enrollment::with(['student', 'program'])->find($enrollmentId) : null,
            'studentFilterName' => $studentId ? User::withTrashed()->whereKey($studentId)->value('name') : null,
            'statuses' => PaymentStatus::cases(),
            'methods' => PaymentMethod::cases(),
        ]);
    }

    /**
     * Menampilkan form pencatatan pembayaran manual.
     */
    public function create(Request $request): View
    {
        return view('admin.payments.create', [
            'enrollments' => $this->payableEnrollments(),
            'methods' => PaymentMethod::cases(),
            'preselectedEnrollmentId' => $request->integer('pendaftaran') ?: null,
        ]);
    }

    /**
     * Mencatat pembayaran baru dengan status Menunggu Verifikasi.
     *
     * Sisa tagihan diperiksa ulang di dalam transaksi dengan mengunci baris pendaftaran agar dua
     * pencatatan bersamaan tidak melebihi tagihan. Bukti disimpan pada disk privat.
     */
    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $proofFile = $request->file('proof');
        $proofPath = $proofFile?->store('payment-proofs', self::PROOF_DISK);

        try {
            $payment = DB::transaction(function () use ($validated, $request, $proofFile, $proofPath): Payment {
                $enrollment = Enrollment::query()->whereKey($validated['enrollment_id'])->lockForUpdate()->firstOrFail();

                $message = StorePaymentRequest::exceedsPayableMessage($enrollment, (string) $validated['amount']);
                if ($message !== null) {
                    throw ValidationException::withMessages(['amount' => $message]);
                }

                $payment = new Payment($validated);
                $payment->enrollment_id = $enrollment->id;
                $payment->status = PaymentStatus::WaitingVerification;
                $payment->recorded_by = $request->user()->id;
                $payment->proof_path = $proofPath;
                $payment->proof_original_name = $proofFile?->getClientOriginalName();
                $payment->save();

                return $payment;
            });
        } catch (Throwable $exception) {
            if ($proofPath !== null) {
                Storage::disk(self::PROOF_DISK)->delete($proofPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', "Pembayaran {$payment->code} sebesar ".Payment::rupiah($payment->amount).' berhasil dicatat dan menunggu verifikasi.');
    }

    /**
     * Menampilkan detail transaksi beserta riwayat transaksi pendaftaran yang sama.
     */
    public function show(Payment $payment): View
    {
        $payment->load(['enrollment.student', 'enrollment.program', 'enrollment.classroom', 'recorder', 'processor']);

        return view('admin.payments.show', [
            'payment' => $payment,
            'enrollment' => $payment->enrollment,
            'relatedPayments' => $payment->enrollment->payments()->latest('paid_on')->latest('id')->get(),
        ]);
    }

    /**
     * Menampilkan bukti pembayaran dari penyimpanan privat. Hanya dapat diakses admin melalui route ini.
     */
    public function proof(Payment $payment): StreamedResponse
    {
        abort_unless($payment->hasProof() && Storage::disk(self::PROOF_DISK)->exists($payment->proof_path), 404);

        return Storage::disk(self::PROOF_DISK)->response(
            $payment->proof_path,
            $payment->proof_original_name ?? basename($payment->proof_path),
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'],
        );
    }

    /**
     * Memverifikasi pembayaran (status Berhasil).
     */
    public function verify(ProcessPaymentRequest $request, Payment $payment): RedirectResponse
    {
        DB::transaction(fn () => $payment->process(PaymentStatus::Paid, $request->user(), $request->validated('reason')));

        return back()->with('success', "Pembayaran {$payment->code} berhasil diverifikasi.");
    }

    /**
     * Menolak pembayaran dengan alasan.
     */
    public function reject(ProcessPaymentRequest $request, Payment $payment): RedirectResponse
    {
        DB::transaction(fn () => $payment->process(PaymentStatus::Rejected, $request->user(), $request->validated('reason')));

        return back()->with('success', "Pembayaran {$payment->code} ditolak.");
    }

    /**
     * Ringkasan pembayaran dari database: total berhasil, menunggu verifikasi, dan tagihan belum lunas.
     *
     * @return array{paid_total: float, waiting_count: int, waiting_total: float, outstanding_count: int, outstanding_total: float}
     */
    private function summary(): array
    {
        $outstandingCents = Enrollment::query()
            ->active()
            ->withSum(['payments as paid_total' => fn (Builder $query) => $query->where('status', PaymentStatus::Paid->value)], 'amount')
            ->get(['id', 'bill_amount'])
            ->map(fn (Enrollment $enrollment) => $enrollment->remainingCents())
            ->filter(fn (int $cents) => $cents > 0);

        return [
            'paid_total' => (float) Payment::query()->where('status', PaymentStatus::Paid->value)->sum('amount'),
            'waiting_count' => Payment::query()->where('status', PaymentStatus::WaitingVerification->value)->count(),
            'waiting_total' => (float) Payment::query()->where('status', PaymentStatus::WaitingVerification->value)->sum('amount'),
            'outstanding_count' => $outstandingCents->count(),
            'outstanding_total' => $outstandingCents->sum() / 100,
        ];
    }

    /**
     * Pendaftaran aktif yang masih memiliki sisa tagihan yang dapat dibayar.
     *
     * @return Collection<int, Enrollment>
     */
    private function payableEnrollments(): Collection
    {
        return Enrollment::query()
            ->active()
            ->with(['student', 'program'])
            ->withSum(['payments as paid_total' => fn (Builder $query) => $query->where('status', PaymentStatus::Paid->value)], 'amount')
            ->withSum(['payments as waiting_total' => fn (Builder $query) => $query->where('status', PaymentStatus::WaitingVerification->value)], 'amount')
            ->latest('enrolled_on')
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->payableCents() > 0)
            ->values();
    }
}
