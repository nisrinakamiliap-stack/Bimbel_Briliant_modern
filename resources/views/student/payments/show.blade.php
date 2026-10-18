@extends('layouts.student')

@section('title', 'Detail Pembayaran — Portal Murid')

@section('content')
<div class="st-container st-page">
    <nav aria-label="Breadcrumb" style="margin-bottom:1.25rem;font-size:.875rem;color:var(--st-muted);">
        <a href="{{ route('student.payments.index') }}" style="color:var(--st-primary);text-decoration:none;">Pembayaran</a>
        <span style="margin:0 .5rem;">›</span>
        <span>{{ $enrollment->program->name }}</span>
    </nav>

    @if (session('success'))
        <div class="st-flash st-flash--success" role="status">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="st-flash st-flash--error" role="alert">
            <ul style="margin:0;padding-left:1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="st-grid-2" style="align-items:flex-start;gap:1.5rem;">
        {{-- Informasi tagihan --}}
        <div>
            <div class="st-detail-card" style="margin-bottom:1.25rem;">
                <div class="st-detail-header">
                    <h1 class="st-detail-title" style="font-size:1.125rem;">{{ $enrollment->program->name }}</h1>
                    <p class="st-detail-meta" style="margin-top:.375rem;">
                        @if ($enrollment->classroom) Kelas: {{ $enrollment->classroom->name }} · @endif
                        <span class="st-status {{ $enrollment->status->badgeClass() }}">{{ $enrollment->status->label() }}</span>
                    </p>
                </div>
                <div class="st-detail-body">
                    <div class="st-info-list">
                        <div class="st-info-row">
                            <span class="st-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
                            <div>
                                <p class="st-info-label">Total Tagihan</p>
                                <p class="st-info-value">Rp {{ number_format((float) $enrollment->bill_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="st-info-row">
                            <span class="st-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg></span>
                            <div>
                                <p class="st-info-label">Sudah Dibayar</p>
                                <p class="st-info-value">Rp {{ number_format($enrollment->paidCents() / 100, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @if ($enrollment->waitingCents() > 0)
                        <div class="st-info-row">
                            <span class="st-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                            <div>
                                <p class="st-info-label">Menunggu Verifikasi</p>
                                <p class="st-info-value">Rp {{ number_format($enrollment->waitingCents() / 100, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @endif
                        <div class="st-info-row">
                            <span class="st-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
                            <div>
                                <p class="st-info-label">Sisa Tagihan</p>
                                <p class="st-info-value" style="color:{{ $enrollment->remainingCents() > 0 ? '#dc2626' : '#059669' }};">
                                    Rp {{ number_format($enrollment->remainingCents() / 100, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                        <div class="st-info-row">
                            <span class="st-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/></svg></span>
                            <div>
                                <p class="st-info-label">Status Tagihan</p>
                                <span class="st-status {{ $enrollment->billingStatusBadgeClass() }}">{{ $enrollment->billingStatusLabel() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Riwayat transaksi --}}
            <h2 class="st-section-title">Riwayat Transaksi</h2>
            @forelse ($enrollment->payments as $payment)
                <div class="st-card" style="margin-bottom:.625rem;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                        <div>
                            <p style="margin:0;font-size:.875rem;font-weight:700;color:var(--st-forest);">{{ $payment->code }}</p>
                            <p style="margin:.25rem 0 0;font-size:.8125rem;color:var(--st-muted);">
                                {{ $payment->method->label() }}
                                · {{ $payment->paid_on ? \Carbon\Carbon::parse($payment->paid_on)->translatedFormat('d M Y') : '—' }}
                            </p>
                            @if ($payment->rejection_reason)
                                <p style="margin:.375rem 0 0;font-size:.8125rem;color:#dc2626;">Alasan: {{ $payment->rejection_reason }}</p>
                            @endif
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <p style="margin:0;font-weight:800;font-size:1rem;">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                            <span class="st-status {{ $payment->status->badgeClass() }}" style="margin-top:.25rem;">{{ $payment->status->label() }}</span>
                            @if ($payment->hasProof())
                                <br>
                                <a href="{{ route('student.payments.proof', $payment) }}" class="st-view-all" style="font-size:.75rem;display:inline-block;margin-top:.25rem;">Lihat Bukti</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p style="color:var(--st-muted);font-size:.9rem;">Belum ada transaksi.</p>
            @endforelse
        </div>

        {{-- Form pembayaran --}}
        @if ($enrollment->payableCents() > 0)
        <div>
            <div class="st-detail-card">
                <div class="st-detail-header">
                    <h2 class="st-detail-title" style="font-size:1.0625rem;">Kirim Bukti Pembayaran</h2>
                    <p style="margin:.25rem 0 0;font-size:.875rem;color:var(--st-muted);">
                        Maksimum yang dapat dibayarkan: <strong>Rp {{ number_format($enrollment->payableCents() / 100, 0, ',', '.') }}</strong>
                    </p>
                </div>
                <div class="st-detail-body">
                    <form action="{{ route('student.payments.store', $enrollment) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="st-form-group">
                            <label for="amount" class="st-label">Nominal Pembayaran (Rp) <span aria-hidden="true" style="color:#dc2626;">*</span></label>
                            <input type="number" id="amount" name="amount" class="st-input @error('amount') is-invalid @enderror"
                                   min="1000" max="{{ $enrollment->payableCents() / 100 }}"
                                   value="{{ old('amount') }}" required
                                   placeholder="Contoh: 500000">
                            @error('amount')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="method" class="st-label">Metode Pembayaran <span aria-hidden="true" style="color:#dc2626;">*</span></label>
                            <select id="method" name="method" class="st-select @error('method') is-invalid @enderror" required>
                                <option value="">Pilih metode</option>
                                @foreach ($methods as $method)
                                    <option value="{{ $method->value }}" @selected(old('method') === $method->value)>{{ $method->label() }}</option>
                                @endforeach
                            </select>
                            @error('method')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="paid_on" class="st-label">Tanggal Pembayaran <span aria-hidden="true" style="color:#dc2626;">*</span></label>
                            <input type="date" id="paid_on" name="paid_on" class="st-input @error('paid_on') is-invalid @enderror"
                                   value="{{ old('paid_on', now()->format('Y-m-d')) }}"
                                   max="{{ now()->format('Y-m-d') }}" required>
                            @error('paid_on')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="proof" class="st-label">Bukti Pembayaran (JPG/PNG/PDF, maks. 5 MB)</label>
                            <input type="file" id="proof" name="proof" class="st-file-input @error('proof') is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.pdf">
                            @error('proof')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="notes" class="st-label">Catatan (opsional)</label>
                            <textarea id="notes" name="notes" class="st-textarea @error('notes') is-invalid @enderror" rows="3" placeholder="No. referensi transfer, nama rekening, dll.">{{ old('notes') }}</textarea>
                            @error('notes')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="st-btn st-btn-primary st-btn-full">
                            Kirim Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @else
            <div class="st-card" style="text-align:center;padding:2rem;">
                <span class="st-status is-success" style="font-size:1rem;padding:.5rem 1rem;">
                    {{ $enrollment->remainingCents() === 0 ? '✓ Tagihan Lunas' : 'Menunggu Verifikasi' }}
                </span>
                <p style="margin:1rem 0 0;color:var(--st-muted);font-size:.9rem;">
                    {{ $enrollment->remainingCents() === 0 ? 'Terima kasih, pembayaran Anda sudah lunas.' : 'Pembayaran Anda sedang dalam proses verifikasi oleh admin.' }}
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
