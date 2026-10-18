@extends('layouts.student')

@section('title', 'Pembayaran — Portal Murid')

@section('content')
<div class="st-container st-page">
    <div class="st-page-header">
        <h1 class="st-page-title">Pembayaran</h1>
        <p class="st-page-sub">Tagihan dan riwayat pembayaran pendaftaran Anda.</p>
    </div>

    @if (session('success'))
        <div class="st-flash st-flash--success" role="status">{{ session('success') }}</div>
    @endif

    @forelse ($enrollments as $enrollment)
        <div class="st-detail-card" style="margin-bottom:1.25rem;">
            <div class="st-detail-header">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                    <div>
                        <h2 class="st-detail-title" style="font-size:1.125rem;">{{ $enrollment->program->name }}</h2>
                        <p style="margin:.25rem 0 0;font-size:.875rem;color:var(--st-muted);">
                            @if ($enrollment->classroom) Kelas: {{ $enrollment->classroom->name }} · @endif
                            Daftar: {{ $enrollment->enrolled_on?->translatedFormat('d M Y') ?? '—' }}
                        </p>
                    </div>
                    <span class="st-status {{ $enrollment->status->badgeClass() }}">{{ $enrollment->status->label() }}</span>
                </div>
            </div>
            <div class="st-detail-body">
                <div class="st-grid-2" style="margin-bottom:1rem;">
                    <div>
                        <p class="st-info-label">Total Tagihan</p>
                        <p style="font-size:1.25rem;font-weight:800;color:var(--st-forest);margin:.25rem 0 0;">
                            Rp {{ number_format((float) $enrollment->bill_amount, 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="st-info-label">Status Tagihan</p>
                        <span class="st-status {{ $enrollment->billingStatusBadgeClass() }}" style="margin-top:.25rem;display:inline-flex;">
                            {{ $enrollment->billingStatusLabel() }}
                        </span>
                        @if ($enrollment->remainingCents() > 0)
                            <p style="font-size:.8125rem;color:var(--st-muted);margin:.25rem 0 0;">
                                Sisa: Rp {{ number_format($enrollment->remainingCents() / 100, 0, ',', '.') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Riwayat pembayaran --}}
                @if ($enrollment->payments->isNotEmpty())
                    <h3 style="font-size:.875rem;font-weight:700;color:var(--st-muted);text-transform:uppercase;letter-spacing:.04em;margin:0 0 .75rem;">Riwayat Transaksi</h3>
                    @foreach ($enrollment->payments as $payment)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.75rem;background:var(--st-mint-bg);border-radius:.625rem;margin-bottom:.5rem;flex-wrap:wrap;">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;">{{ $payment->code }}</p>
                                <p style="margin:.125rem 0 0;font-size:.8125rem;color:var(--st-muted);">
                                    {{ $payment->method->label() }}
                                    · {{ $payment->paid_on ? \Carbon\Carbon::parse($payment->paid_on)->translatedFormat('d M Y') : '—' }}
                                </p>
                            </div>
                            <div style="display:flex;align-items:center;gap:.75rem;flex-shrink:0;">
                                <span style="font-weight:700;font-size:.9375rem;">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span>
                                <span class="st-status {{ $payment->status->badgeClass() }}">{{ $payment->status->label() }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif

                <a href="{{ route('student.payments.show', $enrollment) }}" class="st-btn st-btn-secondary st-btn-sm" style="margin-top:1rem;">
                    Lihat Detail & Kirim Pembayaran →
                </a>
            </div>
        </div>
    @empty
        <div class="st-empty">
            <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
            <p class="st-empty-title">Belum ada tagihan</p>
            <p class="st-empty-desc">Tagihan akan muncul setelah pendaftaran Anda diproses.</p>
        </div>
    @endforelse
</div>
@endsection
