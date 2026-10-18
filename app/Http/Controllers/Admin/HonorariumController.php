<?php

namespace App\Http\Controllers\Admin;

use App\HonorariumStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MarkHonorariumPaidRequest;
use App\Http\Requests\Admin\RecordHonorariumPaymentRequest;
use App\Http\Requests\Admin\StoreHonorariumRequest;
use App\Models\TeacherAttendance;
use App\Models\TeacherHonorarium;
use App\Models\TeacherHonorariumPayment;
use App\Models\User;
use App\PaymentMethod;
use App\TeacherAttendanceStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class HonorariumController extends Controller
{
    /**
     * Jumlah baris per halaman.
     */
    private const PER_PAGE = 15;

    /**
     * Daftar honorarium dengan filter guru, periode, dan status.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'guru'   => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'teacher')],
            'bulan'  => ['nullable', 'integer', 'min:1', 'max:12'],
            'tahun'  => ['nullable', 'integer', 'min:2020', 'max:2099'],
            'status' => ['nullable', Rule::enum(HonorariumStatus::class)],
        ]);

        $teacherId = isset($filters['guru']) ? (int) $filters['guru'] : null;
        $month     = isset($filters['bulan']) ? (int) $filters['bulan'] : null;
        $year      = isset($filters['tahun']) ? (int) $filters['tahun'] : null;
        $status    = isset($filters['status']) ? HonorariumStatus::from($filters['status']) : null;

        $honorariums = TeacherHonorarium::query()
            ->with(['teacher'])
            ->when($teacherId, fn (Builder $q, int $id) => $q->where('teacher_id', $id))
            ->when($month, fn (Builder $q, int $m) => $q->where('period_month', $m))
            ->when($year, fn (Builder $q, int $y) => $q->where('period_year', $y))
            ->when($status, fn (Builder $q, HonorariumStatus $s) => $q->where('status', $s->value))
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderBy('teacher_id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.honorariums.index', [
            'honorariums'   => $honorariums,
            'teachers'      => User::teachers()->orderBy('name')->get(['id', 'name']),
            'statuses'      => HonorariumStatus::cases(),
            'teacherFilter' => $teacherId ? User::withTrashed()->find($teacherId) : null,
            'monthFilter'   => $month,
            'yearFilter'    => $year,
            'statusFilter'  => $status,
            'summary'       => $this->summary(),
        ]);
    }

    /**
     * Form pencatatan honorarium baru.
     */
    public function create(Request $request): View
    {
        $preselectedTeacherId = $request->integer('guru') ?: null;
        $preselectedMonth     = $request->integer('bulan') ?: (int) now()->format('n');
        $preselectedYear      = $request->integer('tahun') ?: (int) now()->format('Y');

        $detectedSessions = 0;
        if ($preselectedTeacherId) {
            $detectedSessions = TeacherAttendance::query()
                ->where('teacher_id', $preselectedTeacherId)
                ->whereYear('session_date', $preselectedYear)
                ->whereMonth('session_date', $preselectedMonth)
                ->whereIn('status', [TeacherAttendanceStatus::Present->value, TeacherAttendanceStatus::Late->value])
                ->count();
        }

        return view('admin.honorariums.create', [
            'teachers'             => User::teachers()->orderBy('name')->get(['id', 'name']),
            'methods'              => PaymentMethod::cases(),
            'preselectedTeacherId' => $preselectedTeacherId,
            'preselectedMonth'     => $preselectedMonth,
            'preselectedYear'      => $preselectedYear,
            'detectedSessions'     => $detectedSessions,
        ]);
    }

    /**
     * Simpan honorarium baru dengan status Draft.
     *
     * Satu guru hanya boleh memiliki satu catatan per periode (unique constraint di DB).
     */
    public function store(StoreHonorariumRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $existing = TeacherHonorarium::query()
            ->where('teacher_id', $validated['teacher_id'])
            ->where('period_year', $validated['period_year'])
            ->where('period_month', $validated['period_month'])
            ->first();

        if ($existing !== null) {
            return redirect()
                ->route('admin.honorariums.show', $existing)
                ->with('warning', 'Honorarium untuk guru dan periode ini sudah pernah dicatat. Berikut data yang ada.');
        }

        $honorarium = new TeacherHonorarium($validated);
        $honorarium->teacher_id   = $validated['teacher_id'];
        $honorarium->period_month = $validated['period_month'];
        $honorarium->period_year  = $validated['period_year'];
        $honorarium->adjustments  = $validated['adjustments'] ?? 0;
        $honorarium->deductions   = $validated['deductions'] ?? 0;
        $honorarium->recalculateAmount();
        $honorarium->status       = HonorariumStatus::Draft;
        $honorarium->created_by   = $request->user()->id;
        $honorarium->save();

        return redirect()
            ->route('admin.honorariums.show', $honorarium)
            ->with('success', 'Honorarium berhasil dicatat dengan status Draft.');
    }

    /**
     * Detail honorarium.
     */
    public function show(TeacherHonorarium $honorarium): View
    {
        $honorarium->load([
            'teacher.teacherProfile',
            'creator',
            'approver',
            'payer',
            'payments.creator',
        ]);

        $attendances = TeacherAttendance::query()
            ->where('teacher_id', $honorarium->teacher_id)
            ->whereYear('session_date', $honorarium->period_year)
            ->whereMonth('session_date', $honorarium->period_month)
            ->with(['schedule.classroom'])
            ->orderBy('session_date')
            ->get();

        return view('admin.honorariums.show', [
            'honorarium'  => $honorarium,
            'attendances' => $attendances,
            'methods'     => PaymentMethod::cases(),
        ]);
    }

    /**
     * Form edit honorarium (hanya saat masih Draft).
     */
    public function edit(TeacherHonorarium $honorarium): View
    {
        abort_unless($honorarium->status->isEditable(), 403, 'Honorarium ini sudah tidak dapat diedit.');

        return view('admin.honorariums.edit', [
            'honorarium' => $honorarium,
        ]);
    }

    /**
     * Perbarui honorarium yang masih Draft.
     */
    public function update(StoreHonorariumRequest $request, TeacherHonorarium $honorarium): RedirectResponse
    {
        abort_unless($honorarium->status->isEditable(), 403, 'Honorarium ini sudah tidak dapat diedit.');

        $validated = $request->validated();
        $honorarium->fill($validated);
        $honorarium->adjustments = $validated['adjustments'] ?? 0;
        $honorarium->deductions  = $validated['deductions'] ?? 0;
        $honorarium->recalculateAmount();
        $honorarium->save();

        return redirect()
            ->route('admin.honorariums.show', $honorarium)
            ->with('success', 'Honorarium berhasil diperbarui.');
    }

    /**
     * Setujui honorarium (Draft → Disetujui / Menunggu Pembayaran).
     */
    public function approve(TeacherHonorarium $honorarium): RedirectResponse
    {
        try {
            DB::transaction(fn () => $honorarium->approve(request()->user()));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Honorarium disetujui. Siap untuk dibayarkan.');
    }

    /**
     * Catat pembayaran honorarium (penuh atau bertahap/sebagian).
     */
    public function recordPayment(RecordHonorariumPaymentRequest $request, TeacherHonorarium $honorarium): RedirectResponse
    {
        $validated = $request->validated();
        $proofFile = $request->file('proof');
        $proofPath = null;
        $proofOriginalName = null;

        if ($proofFile) {
            $proofPath = $proofFile->store('honorarium-proofs', TeacherHonorariumPayment::PROOF_DISK);
            $proofOriginalName = $proofFile->getClientOriginalName();
        }

        try {
            DB::transaction(function () use ($honorarium, $request, $validated, $proofPath, $proofOriginalName) {
                $honorarium->recordPayment(
                    admin: $request->user(),
                    amount: (float) $validated['amount'],
                    method: $validated['payment_method'],
                    paidOn: $validated['paid_on'],
                    referenceNumber: $validated['reference_number'] ?? null,
                    proofPath: $proofPath,
                    proofOriginalName: $proofOriginalName,
                    notes: $validated['notes'] ?? null,
                );
            });
        } catch (ValidationException $e) {
            if ($proofPath) {
                Storage::disk(TeacherHonorariumPayment::PROOF_DISK)->delete($proofPath);
            }

            return back()->withErrors($e->errors());
        } catch (Throwable $e) {
            if ($proofPath) {
                Storage::disk(TeacherHonorariumPayment::PROOF_DISK)->delete($proofPath);
            }

            throw $e;
        }

        $paidFormatted = TeacherHonorarium::rupiah($validated['amount']);

        return back()->with('success', "Pembayaran sebesar {$paidFormatted} berhasil dicatat.");
    }

    /**
     * Unduh/tampilkan berkas bukti pembayaran untuk admin.
     */
    public function proof(TeacherHonorariumPayment $payment): StreamedResponse
    {
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

    /**
     * Cek jumlah sesi terverifikasi untuk guru dan periode via JSON.
     */
    public function checkSessions(Request $request): JsonResponse
    {
        $teacherId = $request->integer('guru');
        $month     = $request->integer('bulan');
        $year      = $request->integer('tahun');

        if (! $teacherId || ! $month || ! $year) {
            return response()->json(['sessions' => 0]);
        }

        $count = TeacherAttendance::query()
            ->where('teacher_id', $teacherId)
            ->whereYear('session_date', $year)
            ->whereMonth('session_date', $month)
            ->whereIn('status', [TeacherAttendanceStatus::Present->value, TeacherAttendanceStatus::Late->value])
            ->count();

        return response()->json(['sessions' => $count]);
    }

    /**
     * Tandai honorarium sebagai Dibayar penuh (kompatibilitas route lama).
     */
    public function markPaid(MarkHonorariumPaidRequest $request, TeacherHonorarium $honorarium): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::transaction(fn () => $honorarium->markPaid(
                $request->user(),
                $validated['payment_method'],
                $validated['paid_on'],
                $validated['notes'] ?? null,
            ));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Honorarium berhasil ditandai sebagai Dibayar.');
    }

    /**
     * Ringkasan honorarium: total dibayar, total disetujui (belum dibayar), total draft.
     *
     * @return array{paid_total: float, approved_total: float, draft_count: int, partial_count: int}
     */
    private function summary(): array
    {
        $paidTotal = (float) TeacherHonorariumPayment::query()->sum('amount');
        if ($paidTotal <= 0) {
            $paidTotal = (float) TeacherHonorarium::query()->where('status', HonorariumStatus::Paid->value)->sum('amount');
        }

        $approvedUnpaid = (float) TeacherHonorarium::query()
            ->whereIn('status', [HonorariumStatus::Approved->value, HonorariumStatus::PartiallyPaid->value])
            ->selectRaw('SUM(amount - paid_amount) as remaining')
            ->value('remaining');

        return [
            'paid_total'     => $paidTotal,
            'approved_total' => (float) ($approvedUnpaid ?? 0),
            'draft_count'    => TeacherHonorarium::query()->where('status', HonorariumStatus::Draft->value)->count(),
            'partial_count'  => TeacherHonorarium::query()->where('status', HonorariumStatus::PartiallyPaid->value)->count(),
        ];
    }
}
