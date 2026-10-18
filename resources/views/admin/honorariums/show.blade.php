@extends('layouts.admin')

@section('title', 'Detail Honorarium')
@section('page_title', 'Honorarium Guru')

@use('App\Models\TeacherHonorarium')
@use('App\HonorariumStatus')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.honorariums.index') }}">Honorarium</a></li>
                <li aria-current="page">{{ $honorarium->teacher->name }} ({{ $honorarium->periodLabel() }})</li>
            </ol>
            <h2>Detail Honorarium Guru</h2>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.honorariums.index') }}" class="admin-btn admin-btn-secondary">
                <x-admin.icon name="arrow-left" :size="16" />
                Kembali
            </a>
            @if ($honorarium->status->isEditable())
                <a href="{{ route('admin.honorariums.edit', $honorarium) }}" class="admin-btn admin-btn-secondary">
                    <x-admin.icon name="edit" :size="16" />
                    Edit
                </a>
            @endif
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
        {{-- Panel utama: info honorarium & rincian --}}
        <div class="is-wide" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <section class="admin-panel" aria-labelledby="honorarium-heading">
                <div class="admin-panel-header">
                    <div>
                        <h3 id="honorarium-heading">
                            Honorarium: {{ $honorarium->periodLabel() }}
                        </h3>
                        <p>{{ $honorarium->teacher->name }}</p>
                    </div>
                    <span class="admin-badge {{ $honorarium->status->badgeClass() }}">
                        {{ $honorarium->status->label() }}
                    </span>
                </div>

                <dl class="admin-detail-list">
                    <div class="admin-detail-row">
                        <dt>Guru</dt>
                        <dd>
                            <a href="{{ route('admin.teachers.show', $honorarium->teacher) }}">
                                {{ $honorarium->teacher->name }}
                            </a>
                        </dd>
                    </div>
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
                        <dt>Total Honor yang Ditetapkan</dt>
                        <dd><strong>{{ TeacherHonorarium::rupiah($honorarium->amount) }}</strong></dd>
                    </div>
                    <div class="admin-detail-row">
                        <dt>Total Sudah Dibayar</dt>
                        <dd style="color: #057A46;"><strong>{{ TeacherHonorarium::rupiah($honorarium->paid_amount) }}</strong></dd>
                    </div>
                    <div class="admin-detail-row">
                        <dt>Sisa yang Belum Dibayar</dt>
                        <dd>
                            @if ($honorarium->remainingAmount() > 0)
                                <strong style="color: #92400e;">{{ TeacherHonorarium::rupiah($honorarium->remainingAmount()) }}</strong>
                            @else
                                <span class="admin-badge is-success">Lunas</span>
                            @endif
                        </dd>
                    </div>
                    @if ($honorarium->notes)
                        <div class="admin-detail-row">
                            <dt>Catatan</dt>
                            <dd>{{ $honorarium->notes }}</dd>
                        </div>
                    @endif
                </dl>

                {{-- Riwayat proses admin --}}
                <div class="admin-panel-header" style="border-top: 1px solid var(--admin-border); margin-top: 0;">
                    <div><h4 style="margin:0; font-size:.9375rem;">Riwayat Penetapan Admin</h4></div>
                </div>
                <dl class="admin-detail-list">
                    <div class="admin-detail-row">
                        <dt>Dicatat oleh</dt>
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
            </section>

            {{-- Riwayat Transaksi Pembayaran --}}
            <section class="admin-panel" aria-labelledby="payments-list-heading">
                <div class="admin-panel-header">
                    <div>
                        <h3 id="payments-list-heading">Riwayat Transaksi Pembayaran</h3>
                        <p>Catatan transaksi pembayaran yang telah dilakukan ke guru ini.</p>
                    </div>
                </div>

                @if ($honorarium->payments->isEmpty())
                    <div style="padding: 1.5rem; text-align: center; color: var(--admin-muted);">
                        Belum ada pembayaran yang dicatat untuk honorarium ini.
                    </div>
                @else
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th class="admin-table-num">Nominal</th>
                                    <th>Metode</th>
                                    <th>No. Referensi</th>
                                    <th>Bukti</th>
                                    <th>Petugas</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($honorarium->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->paid_on?->translatedFormat('d M Y') ?? '-' }}</td>
                                        <td class="admin-table-num">
                                            <strong>{{ TeacherHonorarium::rupiah($payment->amount) }}</strong>
                                        </td>
                                        <td>{{ $payment->payment_method?->label() ?? '-' }}</td>
                                        <td>{{ $payment->reference_number ?? '-' }}</td>
                                        <td>
                                            @if ($payment->hasProof())
                                                <a href="{{ route('admin.honorariums.proof', $payment) }}"
                                                    target="_blank" rel="noopener"
                                                    class="admin-btn admin-btn-secondary admin-btn-sm" style="display: inline-flex; align-items: center; gap: .25rem;">
                                                    <x-admin.icon name="eye" :size="14" />
                                                    Lihat Bukti
                                                </a>
                                            @else
                                                <span class="admin-muted">Tidak ada</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->creator?->name ?? '-' }}</td>
                                        <td>{{ $payment->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- Sesi Mengajar Riil dari Absensi --}}
            <section class="admin-panel" aria-labelledby="attendances-list-heading">
                <div class="admin-panel-header">
                    <div>
                        <h3 id="attendances-list-heading">Data Kehadiran Mengajar pada Periode Ini</h3>
                        <p>Daftar sesi check-in guru yang tercatat dalam sistem absensi (Total: {{ $attendances->count() }} sesi).</p>
                    </div>
                </div>

                @if ($attendances->isEmpty())
                    <div style="padding: 1.5rem; text-align: center; color: var(--admin-muted);">
                        Tidak ditemukan catatan check-in absensi guru pada bulan {{ $honorarium->periodLabel() }}.
                    </div>
                @else
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kelas</th>
                                    <th>Check-In</th>
                                    <th>Check-Out</th>
                                    <th>Status Absensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendances as $att)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($att->session_date)->translatedFormat('l, d M Y') }}</td>
                                        <td>{{ $att->schedule?->classroom?->name ?? 'Kelas' }}</td>
                                        <td>{{ $att->checked_in_at?->format('H:i') ?? '-' }}</td>
                                        <td>{{ $att->checked_out_at?->format('H:i') ?? '-' }}</td>
                                        <td>
                                            <span class="admin-badge {{ $att->status->badgeClass() }}">
                                                {{ $att->status->label() }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        {{-- Aksi --}}
        <aside style="display: flex; flex-direction: column; gap: 1.5rem;">
            {{-- Form Persetujuan Draft --}}
            @if ($honorarium->status->isApprovable())
                <section class="admin-panel" aria-labelledby="approve-heading">
                    <div class="admin-panel-header">
                        <div><h3 id="approve-heading">Setujui Honorarium</h3></div>
                    </div>
                    <div class="admin-form-body">
                        <p style="font-size:.875rem; color: var(--admin-muted);">
                            Setelah disetujui, penetapan honorarium ini siap untuk dibayarkan (penuh maupun dicicil).
                        </p>
                        <form method="POST" action="{{ route('admin.honorariums.approve', $honorarium) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; justify-content: center;"
                                data-confirm="Setujui honorarium {{ TeacherHonorarium::rupiah($honorarium->amount) }} untuk {{ $honorarium->teacher->name }}?">
                                <x-admin.icon name="check" :size="16" />
                                Setujui Honorarium
                            </button>
                        </form>
                    </div>
                </section>
            @endif

            {{-- Form Catat Pembayaran --}}
            @if ($honorarium->status->isPayable() && $honorarium->remainingAmount() > 0)
                <section class="admin-panel" aria-labelledby="pay-heading">
                    <div class="admin-panel-header">
                        <div>
                            <h3 id="pay-heading">Catat Pembayaran</h3>
                            <p style="margin: 0; font-size: .8125rem; color: #92400e;">
                                Sisa belum dibayar: <strong>{{ TeacherHonorarium::rupiah($honorarium->remainingAmount()) }}</strong>
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.honorariums.record-payment', $honorarium) }}"
                        class="admin-form-body" enctype="multipart/form-data" novalidate>
                        @csrf

                        <div class="admin-form-group">
                            <label for="amount" class="admin-label">Nominal Pembayaran (Rp) <span class="admin-required">*</span></label>
                            <input type="text" id="amount" name="amount"
                                class="admin-input @error('amount') is-error @enderror"
                                value="{{ old('amount', (int) $honorarium->remainingAmount()) }}"
                                placeholder="Contoh: {{ (int) $honorarium->remainingAmount() }}" required>
                            <p class="admin-hint">Bisa diisi nominal penuh atau sebagian (cicilan).</p>
                            @error('amount')
                                <p class="admin-hint is-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="admin-form-group">
                            <label for="paid_on" class="admin-label">Tanggal Transfer / Pembayaran <span class="admin-required">*</span></label>
                            <input type="date" id="paid_on" name="paid_on"
                                class="admin-input @error('paid_on') is-error @enderror"
                                value="{{ old('paid_on', now()->format('Y-m-d')) }}"
                                max="{{ now()->format('Y-m-d') }}" required>
                            @error('paid_on')
                                <p class="admin-hint is-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="admin-form-group">
                            <label for="payment_method" class="admin-label">Metode Pembayaran <span class="admin-required">*</span></label>
                            <select id="payment_method" name="payment_method"
                                class="admin-input admin-select @error('payment_method') is-error @enderror" required>
                                <option value="">Pilih metode pembayaran</option>
                                @foreach ($methods as $method)
                                    <option value="{{ $method->value }}" @selected(old('payment_method') === $method->value)>
                                        {{ $method->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <p class="admin-hint is-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="admin-form-group">
                            <label for="reference_number" class="admin-label">Nomor Referensi Transfer / Kwitansi</label>
                            <input type="text" id="reference_number" name="reference_number"
                                class="admin-input @error('reference_number') is-error @enderror"
                                value="{{ old('reference_number') }}"
                                placeholder="Contoh: TRF-BCA-20261018-001 (opsional)">
                            @error('reference_number')
                                <p class="admin-hint is-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="admin-form-group">
                            <label for="proof" class="admin-label">Unggah Bukti Pembayaran (Struk/Resi)</label>
                            <input type="file" id="proof" name="proof"
                                class="admin-input @error('proof') is-error @enderror"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <p class="admin-hint">Format JPG, PNG, atau PDF (maksimal 2 MB).</p>
                            @error('proof')
                                <p class="admin-hint is-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="admin-form-group">
                            <label for="notes_pay" class="admin-label">Catatan Tambahan</label>
                            <textarea id="notes_pay" name="notes" rows="2"
                                class="admin-input @error('notes') is-error @enderror"
                                placeholder="Keterangan transaksi (opsional)">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="admin-hint is-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; justify-content: center;">
                            <x-admin.icon name="check" :size="16" />
                            Simpan Pembayaran
                        </button>
                    </form>
                </section>
            @endif

            @if ($honorarium->status === \App\HonorariumStatus::Paid)
                <div class="admin-panel" style="padding: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: .5rem; color: #057A46; font-weight: 700;">
                        <x-admin.icon name="check" :size="20" />
                        <span>Honorarium Lunas</span>
                    </div>
                    <p style="font-size:.875rem; color: var(--admin-muted); margin:.5rem 0 0;">
                        Seluruh nominal honorarium periode ini telah selesai dibayarkan kepada guru.
                    </p>
                </div>
            @endif
        </aside>
    </div>
@endsection
