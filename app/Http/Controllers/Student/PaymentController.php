<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use App\PaymentMethod;
use App\PaymentStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();

        $enrollments = Enrollment::query()
            ->where('student_id', $student->id)
            ->with(['program', 'classroom', 'payments' => fn ($q) => $q->latest('paid_on')])
            ->withSum(['payments as paid_total' => fn ($q) => $q->where('status', PaymentStatus::Paid->value)], 'amount')
            ->withSum(['payments as waiting_total' => fn ($q) => $q->where('status', PaymentStatus::WaitingVerification->value)], 'amount')
            ->latest()
            ->get();

        return view('student.payments.index', [
            'enrollments' => $enrollments,
        ]);
    }

    public function show(Request $request, Enrollment $enrollment): View
    {
        if ($enrollment->student_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $enrollment->load(['program', 'classroom', 'payments' => fn ($q) => $q->latest('paid_on')]);
        $enrollment->loadSum(['payments as paid_total' => fn ($q) => $q->where('status', PaymentStatus::Paid->value)], 'amount');
        $enrollment->loadSum(['payments as waiting_total' => fn ($q) => $q->where('status', PaymentStatus::WaitingVerification->value)], 'amount');

        $methods = PaymentMethod::cases();

        return view('student.payments.show', [
            'enrollment' => $enrollment,
            'methods' => $methods,
        ]);
    }

    public function store(Request $request, Enrollment $enrollment): RedirectResponse
    {
        if ($enrollment->student_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $payable = $enrollment->payableCents();
        if ($payable <= 0) {
            return back()->withErrors(['amount' => 'Tagihan sudah lunas atau sedang menunggu verifikasi.']);
        }

        $validated = $request->validate([
            'amount'  => ['required', 'numeric', 'min:1000', 'max:' . ($payable / 100)],
            'method'  => ['required', Rule::enum(PaymentMethod::class)],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'notes'   => ['nullable', 'string', 'max:500'],
            'proof'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payments/proofs', 'local');
        }

        $payment = new Payment($validated);
        $payment->enrollment_id = $enrollment->id;
        $payment->status = PaymentStatus::WaitingVerification;
        if ($proofPath !== null) {
            $payment->proof_path = $proofPath;
            $payment->proof_original_name = $request->file('proof')->getClientOriginalName();
        }
        $payment->save();

        return redirect()->route('student.payments.show', $enrollment)
            ->with('success', 'Pembayaran berhasil dikirim dan sedang menunggu verifikasi admin.');
    }

    public function proof(Request $request, Payment $payment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if ($payment->enrollment->student_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (! $payment->hasProof()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('local')->download($payment->proof_path, $payment->proof_original_name ?? 'bukti-pembayaran');
    }
}
