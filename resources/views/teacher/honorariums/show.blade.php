@extends('layouts.teacher')

@section('title', 'Detail Honorarium')

@use('App\Models\TeacherHonorarium')

@section('content')
    <div class="teacher-page-head">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('teacher.honorariums.index') }}">Honorarium Saya</a></li>
                <li aria-current="page">{{ $honorarium->periodLabel() }}</li>
            </ol>
            <h1>Detail Honorarium</h1>
        </div>
        <a href="{{ route('teacher.honorariums.index') }}" class="admin-btn admin-btn-secondary">
            <x-admin.icon name="arrow-left" :size="16" />
            Kembali
        </a>
    </div>

    <x-admin.flash />

    <div class="teacher-columns">
        <section class="admin-panel" aria-labelledby="detail-heading">
            <div class="admin-panel-header">
                <div>
                    <h2 id="detail-heading">{{ $honorarium->periodLabel() }}</h2>
                </div>
                <span class="admin-badge {{ $honorarium->status->badgeClass() }}">
                    {{ $honorarium->status->label() }}
                </span>
            </div>

            <dl class="admin-detail-list">
                <div class="admin-detail-row">
                    <dt>Periode</dt>
                    <dd>{{ $honorarium->periodLabel() }}</dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Jumlah Sesi Mengajar</dt>
                    <dd>{{ $honorarium->session_count }} sesi</dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Tarif per Sesi</dt>
                    <dd>{{ TeacherHonorarium::rupiah($honorarium->rate_per_session) }}</dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Honor Pokok Sesi</dt>
                    <dd>{{ TeacherHonorarium::rupiah($honorarium->base_amount) }}</dd>
                </div>
                @if ($honorarium->adjustments > 0)
                    <div class="admin-detail-row">
                        <dt>Penyesuaian / Tunjangan (+)</dt>
                        <dd style="color: #057A46;">+{{ TeacherHonorarium::rupiah($honorarium->adjustments) }}</dd>
                    </div>
                @endif
                @if ($honorarium->deductions > 0)
                    <div class="admin-detail-row">
                        <dt>Potongan Disetujui (-)</dt>
                        <dd style="color: #991b1b;">-{{ TeacherHonorarium::rupiah($honorarium->deductions) }}</dd>
                    </div>
                @endif
                <div class="admin-detail-row">
                    <dt>Total Honor Ditetapkan</dt>
                    <dd><strong>{{ TeacherHonorarium::rupiah($honorarium->amount) }}</strong></dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Total Sudah Dibayar</dt>
                    <dd style="color: #057A46;"><strong>{{ TeacherHonorarium::rupiah($honorarium->paid_amount) }}</strong></dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Sisa Belum Dibayar</dt>
                    <dd>
                        @if ($honorarium->remainingAmount() > 0)
                            <strong style="color: #92400e;">{{ TeacherHonorarium::rupiah($honorarium->remainingAmount()) }}</strong>
                        @else
                            <span class="admin-badge is-success">Lunas</span>
                        @endif
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Status Pembayaran</dt>
                    <dd>
                        <span class="admin-badge {{ $honorarium->status->badgeClass() }}">
                            {{ $honorarium->status->label() }}
                        </span>
                    </dd>
                </div>
                @if ($honorarium->notes)
                    <div class="admin-detail-row">
                        <dt>Catatan</dt>
                        <dd>{{ $honorarium->notes }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="admin-panel" aria-labelledby="payments-heading">
            <div class="admin-panel-header">
                <div><h2 id="payments-heading">Riwayat Transaksi Pembayaran</h2></div>
            </div>

            @if ($honorarium->payments->isEmpty())
                <div style="padding: 1.5rem; text-align: center; color: var(--admin-muted);">
                    Belum ada pembayaran yang dicatat untuk honorarium periode ini.
                </div>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th class="admin-table-num">Nominal</th>
                                <th>Metode</th>
                                <th>Referensi</th>
                                <th>Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($honorarium->payments as $payment)
                                <tr>
                                    <td>{{ $payment->paid_on?->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td class="admin-table-num">
                                        <strong style="color: #057A46;">{{ TeacherHonorarium::rupiah($payment->amount) }}</strong>
                                    </td>
                                    <td>{{ $payment->payment_method?->label() ?? '-' }}</td>
                                    <td>{{ $payment->reference_number ?? '-' }}</td>
                                    <td>
                                        @if ($payment->hasProof())
                                            <a href="{{ route('teacher.honorariums.proof', $payment) }}"
                                                target="_blank" rel="noopener"
                                                class="admin-btn admin-btn-secondary admin-btn-sm"
                                                style="display: inline-flex; align-items: center; gap: .25rem;">
                                                <x-admin.icon name="eye" :size="14" />
                                                Lihat
                                            </a>
                                        @else
                                            <span class="admin-muted">Tidak ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="admin-panel-header" style="border-top: 1px solid var(--admin-border); margin-top: 0;">
                <div><h3 style="margin:0; font-size:.9375rem;">Riwayat Penetapan</h3></div>
            </div>
            <dl class="admin-detail-list">
                <div class="admin-detail-row">
                    <dt>Dicatat oleh Admin</dt>
                    <dd>{{ $honorarium->creator?->name ?? '-' }}
                        @if ($honorarium->created_at)
                            <span class="admin-muted"> · {{ $honorarium->created_at->translatedFormat('d M Y, H:i') }}</span>
                        @endif
                    </dd>
                </div>
                @if ($honorarium->approver)
                    <div class="admin-detail-row">
                        <dt>Disetujui oleh</dt>
                        <dd>{{ $honorarium->approver->name }}
                            @if ($honorarium->approved_at)
                                <span class="admin-muted"> · {{ $honorarium->approved_at->translatedFormat('d M Y, H:i') }}</span>
                            @endif
                        </dd>
                    </div>
                @endif
            </dl>

            <div style="padding: 1rem 1.25rem;">
                <p style="font-size: .8125rem; color: var(--admin-muted); margin: 0;">
                    Jika ada pertanyaan mengenai honorarium ini, hubungi admin melalui
                    <a href="{{ route('messages.index') }}" class="teacher-link">Pesan</a>.
                </p>
            </div>
        </section>
    </div>
@endsection
