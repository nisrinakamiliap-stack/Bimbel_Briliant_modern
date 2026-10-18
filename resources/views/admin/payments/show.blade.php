@extends('layouts.admin')

@section('title', 'Transaksi '.$payment->code)
@section('page_title', 'Pembayaran')

@use('App\Models\Payment')

@php
$student = $enrollment->student;
@endphp

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.payments.index') }}">Pembayaran</a></li>
                <li aria-current="page">{{ $payment->code }}</li>
            </ol>
            <h2>Detail Transaksi</h2>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.payments.index') }}" class="admin-btn admin-btn-secondary">
                <x-admin.icon name="arrow-left" :size="16" />
                Kembali
            </a>
            <a href="{{ route('admin.payments.index', ['murid' => $student->id]) }}" class="admin-btn admin-btn-secondary">Riwayat Murid</a>
        </div>
    </div>

    <x-admin.flash />

    @error('status')
        <div class="admin-alert is-error" role="alert">
            <x-admin.icon name="alert" :size="18" />
            <span>{{ $message }}</span>
        </div>
    @enderror

    <div class="admin-grid">
        <section class="admin-panel is-wide" aria-labelledby="payment-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="payment-heading">{{ $payment->code }}</h3>
                    <p>{{ Payment::rupiah($payment->amount) }} · {{ $payment->method->label() }}</p>
                </div>
                <span class="admin-badge {{ $payment->status->badgeClass() }}">{{ $payment->status->label() }}</span>
            </div>
            <dl class="admin-detail-list">
                <div>
                    <dt>Murid</dt>
                    <dd>
                        {{ $student->name }}
                        <span class="admin-cell-sub">{{ $student->email }}{{ $student->trashed() ? ' · akun dihapus' : '' }}</span>
                    </dd>
                </div>
                <div>
                    <dt>Pendaftaran</dt>
                    <dd>
                        <a href="{{ route('admin.enrollments.show', $enrollment) }}" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;">{{ $enrollment->program->name }}</a>
                        <span class="admin-cell-sub">{{ $enrollment->classroom?->name ?? 'Kelas belum ditentukan' }} · {{ $enrollment->status->label() }}</span>
                    </dd>
                </div>
                <div>
                    <dt>Nominal Dibayar</dt>
                    <dd style="font-weight: 700;">{{ Payment::rupiah($payment->amount) }}</dd>
                </div>
                <div>
                    <dt>Tanggal Pembayaran</dt>
                    <dd>{{ $payment->paid_on->translatedFormat('d F Y') }}</dd>
                </div>
                <div>
                    <dt>Dicatat Oleh</dt>
                    <dd>{{ $payment->recorder?->name ?? '—' }} <span class="admin-cell-sub">{{ $payment->created_at?->translatedFormat('d F Y, H:i') }}</span></dd>
                </div>
                <div>
                    <dt>{{ $payment->status->isProcessable() ? 'Diproses Oleh' : ($payment->status->value === 'paid' ? 'Diverifikasi Oleh' : 'Ditolak Oleh') }}</dt>
                    <dd>
                        @if ($payment->processed_at)
                            {{ $payment->processor?->name ?? '—' }} <span class="admin-cell-sub">{{ $payment->processed_at->translatedFormat('d F Y, H:i') }}</span>
                        @else
                            Belum diproses
                        @endif
                    </dd>
                </div>
                @if ($payment->rejection_reason)
                    <div style="grid-column: 1 / -1;">
                        <dt>Alasan Penolakan</dt>
                        <dd style="color: #991b1b; white-space: pre-line;">{{ $payment->rejection_reason }}</dd>
                    </div>
                @endif
                <div style="grid-column: 1 / -1;">
                    <dt>Catatan</dt>
                    <dd style="white-space: pre-line;">{{ $payment->notes ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="admin-panel" aria-labelledby="billing-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="billing-heading">Status Tagihan</h3>
                    <p>Pendaftaran {{ $enrollment->program->name }}</p>
                </div>
                <span class="admin-badge {{ $enrollment->billingStatusBadgeClass() }}">{{ $enrollment->billingStatusLabel() }}</span>
            </div>
            <dl class="admin-detail-list">
                <div>
                    <dt>Total Tagihan</dt>
                    <dd>{{ Payment::rupiah($enrollment->bill_amount) }}</dd>
                </div>
                <div>
                    <dt>Sudah Dibayar</dt>
                    <dd>{{ Payment::rupiah($enrollment->paidCents() / 100) }}</dd>
                </div>
                <div>
                    <dt>Menunggu Verifikasi</dt>
                    <dd>{{ Payment::rupiah($enrollment->waitingCents() / 100) }}</dd>
                </div>
                <div>
                    <dt>Sisa Tagihan</dt>
                    <dd style="font-weight: 700;">{{ Payment::rupiah($enrollment->remainingCents() / 100) }}</dd>
                </div>
            </dl>
        </section>

        <section class="admin-panel" aria-labelledby="proof-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="proof-heading">Bukti Pembayaran</h3>
                    <p>{{ $payment->hasProof() ? $payment->proof_original_name : 'Tidak ada bukti yang diunggah.' }}</p>
                </div>
                @if ($payment->hasProof())
                    <a href="{{ route('admin.payments.proof', $payment) }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary admin-btn-sm" id="btn-view-proof">
                        <x-admin.icon name="eye" :size="14" /> Lihat Bukti
                    </a>
                @endif
            </div>
            @if ($payment->proofIsImage())
                <div style="padding: 1rem 1.25rem;">
                    <img src="{{ route('admin.payments.proof', $payment) }}" alt="Bukti pembayaran {{ $payment->code }}" style="max-width: 100%; max-height: 24rem; border-radius: .625rem; border: 1px solid var(--admin-border);">
                </div>
            @elseif (! $payment->hasProof())
                <x-admin.empty-state title="Tanpa bukti" icon="inbox">
                    Pastikan pembayaran sudah diterima di rekening atau kas sebelum memverifikasi.
                </x-admin.empty-state>
            @endif
        </section>

        @if ($payment->status->isProcessable())
            <section class="admin-panel is-wide" aria-labelledby="process-heading" id="panel-process">
                <div class="admin-panel-header">
                    <div>
                        <h3 id="process-heading">Verifikasi Transaksi</h3>
                        <p>Periksa nominal dan bukti. Verifikasi bersifat final dan tidak dapat diproses ulang.</p>
                    </div>
                </div>
                <div class="admin-form" style="display: grid; gap: 1.25rem;">
                    <form action="{{ route('admin.payments.verify', $payment) }}" method="POST"
                          data-confirm="Verifikasi transaksi {{ $payment->code }} sebesar {{ Payment::rupiah($payment->amount) }}? Pembayaran akan dihitung sebagai Berhasil dan mengurangi sisa tagihan."
                          data-confirm-title="Verifikasi Pembayaran" data-confirm-button="Ya, Verifikasi">
                        @csrf
                        @method('PATCH')
                        <label for="verify-reason" class="admin-label">Catatan verifikasi <span class="is-optional">(opsional)</span></label>
                        <textarea id="verify-reason" name="reason" class="admin-textarea" rows="2" maxlength="1000" placeholder="Contoh: Dana sudah masuk ke rekening BCA."></textarea>
                        <div class="admin-form-actions" style="margin-top: .75rem; padding-top: 0; border-top: 0;">
                            <button type="submit" class="admin-btn admin-btn-primary" id="btn-verify-payment">
                                <x-admin.icon name="check" :size="16" /> Verifikasi Pembayaran
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('admin.payments.reject', $payment) }}" method="POST"
                          data-confirm="Tolak transaksi {{ $payment->code }}? Alasan penolakan akan tersimpan dan transaksi tidak dapat diverifikasi lagi."
                          data-confirm-title="Tolak Pembayaran" data-confirm-button="Ya, Tolak">
                        @csrf
                        @method('PATCH')
                        <label for="reject-reason" class="admin-label">Alasan penolakan <span class="is-required" aria-hidden="true">*</span></label>
                        <textarea id="reject-reason" name="reason" class="admin-textarea {{ $errors->has('reason') ? 'is-invalid' : '' }}" rows="2" maxlength="1000" required
                                  placeholder="Contoh: Nominal transfer tidak sesuai atau bukti tidak terbaca."
                                  @error('reason') aria-invalid="true" aria-describedby="reject-reason-error" @enderror>{{ old('reason') }}</textarea>
                        @error('reason') <p id="reject-reason-error" class="admin-error">{{ $message }}</p> @enderror
                        <div class="admin-form-actions" style="margin-top: .75rem; padding-top: 0; border-top: 0;">
                            <button type="submit" class="admin-btn admin-btn-danger-outline" id="btn-reject-payment">Tolak Pembayaran</button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        <section class="admin-panel is-wide" aria-labelledby="related-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="related-heading">Riwayat Transaksi Pendaftaran</h3>
                    <p>Seluruh transaksi untuk pendaftaran {{ $student->name }} — {{ $enrollment->program->name }}.</p>
                </div>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No. Transaksi</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Metode</th>
                            <th scope="col">Nominal</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($relatedPayments as $relatedPayment)
                            <tr @if ($relatedPayment->is($payment)) style="background-color: var(--admin-mint-bg);" aria-current="true" @endif>
                                <td><a href="{{ route('admin.payments.show', $relatedPayment) }}" class="admin-cell-name" style="text-decoration: none;">{{ $relatedPayment->code }}</a></td>
                                <td>{{ $relatedPayment->paid_on->translatedFormat('d M Y') }}</td>
                                <td>{{ $relatedPayment->method->label() }}</td>
                                <td>{{ Payment::rupiah($relatedPayment->amount) }}</td>
                                <td><span class="admin-badge {{ $relatedPayment->status->badgeClass() }}">{{ $relatedPayment->status->label() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
