@extends('layouts.teacher')

@section('title', 'Honorarium Saya')

@use('App\Models\TeacherHonorarium')
@use('App\HonorariumStatus')

@section('content')
    <x-admin.flash />

    <div class="teacher-page-head">
        <div>
            <h1>Honorarium Saya</h1>
            <p>Ringkasan penetapan honor mengajar dan riwayat pembayaran resmi dari Bimbel Sentra Edukasi.</p>
        </div>
    </div>

    {{-- Filter Periode --}}
    <section class="teacher-section" aria-labelledby="filter-heading" style="margin-bottom: 1.5rem;">
        <h2 id="filter-heading" class="sr-only">Pilih Periode</h2>
        <div class="teacher-honor-card" style="padding: 1rem 1.25rem;">
            <form action="{{ route('teacher.honorariums.index') }}" method="GET" class="teacher-period-form" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 10rem;">
                    <label for="filter-bulan" class="teacher-honor-label" style="display: block; margin-bottom: .375rem;">Bulan Periode</label>
                    <select id="filter-bulan" name="bulan" class="admin-input admin-select" style="width: 100%; min-height: 2.75rem;">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($periodData['month'] === $m)>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="flex: 1; min-width: 8rem;">
                    <label for="filter-tahun" class="teacher-honor-label" style="display: block; margin-bottom: .375rem;">Tahun</label>
                    <input type="number" id="filter-tahun" name="tahun" class="admin-input" value="{{ $periodData['year'] }}" min="2020" max="2099" style="width: 100%; min-height: 2.75rem;">
                </div>
                <div>
                    <button type="submit" class="admin-btn admin-btn-primary" style="min-height: 2.75rem; padding-left: 1.25rem; padding-right: 1.25rem;">
                        Tampilkan Periode
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- Ringkasan Periode Terpilih --}}
    <section class="teacher-section" aria-labelledby="summary-heading">
        <div class="teacher-section-head">
            <h2 id="summary-heading">Ringkasan Honor: {{ $periodData['label'] }}</h2>
            @if ($periodData['status'])
                <span class="admin-badge {{ $periodData['status']->badgeClass() }}" style="font-size: .875rem; padding: .25rem .75rem;">
                    {{ $periodData['status']->label() }}
                </span>
            @else
                <span class="admin-badge is-neutral" style="font-size: .875rem; padding: .25rem .75rem;">
                    Belum Ditetapkan
                </span>
            @endif
        </div>

        <div class="teacher-honor-summary">
            {{-- Total Honor --}}
            <article class="teacher-honor-card">
                <p class="teacher-honor-label">Total Honor Periode</p>
                @if ($periodData['is_configured'])
                    <p class="teacher-honor-value">{{ TeacherHonorarium::rupiah($periodData['total_amount']) }}</p>
                    <p class="teacher-honor-note">Ditetapkan oleh Admin</p>
                @else
                    <p class="teacher-honor-value" style="font-size: 1.125rem; color: var(--admin-muted);">Belum Tersedia</p>
                    <p class="teacher-honor-note">Perlu dikonfigurasi oleh Admin</p>
                @endif
            </article>

            {{-- Sudah Dibayar --}}
            <article class="teacher-honor-card">
                <p class="teacher-honor-label">Sudah Dibayar</p>
                <p class="teacher-honor-value" style="color: #057A46;">
                    {{ TeacherHonorarium::rupiah($periodData['paid_amount']) }}
                </p>
                <p class="teacher-honor-note">
                    @if ($periodData['paid_amount'] > 0)
                        {{ $periodData['payments']->count() }} kali transaksi pembayaran
                    @else
                        Belum ada transfer tercatat
                    @endif
                </p>
            </article>

            {{-- Sisa Honor --}}
            <article class="teacher-honor-card">
                <p class="teacher-honor-label">Sisa Belum Dibayar</p>
                @if ($periodData['is_configured'])
                    @if ($periodData['remaining_amount'] > 0)
                        <p class="teacher-honor-value" style="color: #92400e;">
                            {{ TeacherHonorarium::rupiah($periodData['remaining_amount']) }}
                        </p>
                        <p class="teacher-honor-note">Menunggu pencairan oleh Admin</p>
                    @else
                        <p class="teacher-honor-value" style="color: #057A46;">Rp0</p>
                        <p class="teacher-honor-note">Honorarium telah lunas dibayar</p>
                    @endif
                @else
                    <p class="teacher-honor-value" style="font-size: 1.125rem; color: var(--admin-muted);">Belum Tersedia</p>
                    <p class="teacher-honor-note">Menunggu penetapan nominal</p>
                @endif
            </article>

            {{-- Sesi Mengajar --}}
            <article class="teacher-honor-card">
                <p class="teacher-honor-label">Sesi Mengajar</p>
                <p class="teacher-honor-value">{{ $periodData['session_count'] }} sesi</p>
                <p class="teacher-honor-note">
                    {{ $periodData['verified_attendance_count'] }} sesi terverifikasi dari absensi
                </p>
            </article>
        </div>
    </section>

    {{-- Rincian Honor Periode --}}
    <section class="teacher-section" aria-labelledby="detail-heading">
        <div class="teacher-section-head">
            <h2 id="detail-heading">Rincian Perhitungan Honor</h2>
            @if ($periodData['honorarium'])
                <a href="{{ route('teacher.honorariums.show', $periodData['honorarium']) }}" class="teacher-link" style="font-size: .875rem;">
                    Buka Halaman Detail
                </a>
            @endif
        </div>

        <div class="teacher-honor-card" style="padding: 0; overflow: hidden;">
            @if (! $periodData['is_configured'])
                <div style="padding: 1.5rem; background-color: rgba(7, 155, 88, 0.05); border-bottom: 1px solid var(--admin-border);">
                    <div style="display: flex; gap: .75rem; align-items: flex-start;">
                        <x-admin.icon name="alert" :size="20" style="color: var(--teacher-action); flex-shrink: 0; margin-top: .125rem;" />
                        <div>
                            <strong style="color: var(--admin-forest);">Konfigurasi Tarif Belum Tersedia</strong>
                            <p style="margin: .25rem 0 0; font-size: .875rem; color: var(--admin-forest); line-height: 1.5;">
                                Nominal honorarium untuk periode <strong>{{ $periodData['label'] }}</strong> belum ditetapkan oleh Admin lembaga Bimbel Sentra Edukasi.
                                Sistem tidak menggunakan asumsi tarif sepihak. Setelah Admin mengonfigurasi dan menyetujui honorarium periode ini, rincian nominal dan status pembayaran akan langsung muncul di halaman ini.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <dl class="admin-detail-list" style="margin: 0;">
                <div class="admin-detail-row">
                    <dt>Periode Kalender</dt>
                    <dd><strong>{{ $periodData['label'] }}</strong></dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Sesi Mengajar Memenuhi Syarat</dt>
                    <dd>
                        {{ $periodData['verified_attendance_count'] }} sesi
                        <span class="admin-muted">(Berdasarkan absensi hadir/terlambat guru yang valid)</span>
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Tarif per Sesi</dt>
                    <dd>
                        @if ($periodData['is_configured'])
                            <strong>{{ TeacherHonorarium::rupiah($periodData['rate_per_session']) }}</strong> / sesi
                        @else
                            <span class="admin-muted">Belum tersedia (perlu dikonfigurasi oleh Admin)</span>
                        @endif
                    </dd>
                </div>
                @if ($periodData['is_configured'])
                    <div class="admin-detail-row">
                        <dt>Honor Pokok Sesi</dt>
                        <dd>{{ TeacherHonorarium::rupiah($periodData['base_amount']) }}</dd>
                    </div>
                    @if ($periodData['adjustments'] > 0)
                        <div class="admin-detail-row">
                            <dt>Penyesuaian / Tunjangan Disetujui (+)</dt>
                            <dd style="color: #057A46;">+{{ TeacherHonorarium::rupiah($periodData['adjustments']) }}</dd>
                        </div>
                    @endif
                    @if ($periodData['deductions'] > 0)
                        <div class="admin-detail-row">
                            <dt>Potongan Disetujui (-)</dt>
                            <dd style="color: #991b1b;">-{{ TeacherHonorarium::rupiah($periodData['deductions']) }}</dd>
                        </div>
                    @endif
                @endif
                <div class="admin-detail-row">
                    <dt>Total Honor yang Ditetapkan Admin</dt>
                    <dd>
                        @if ($periodData['is_configured'])
                            <strong style="font-size: 1.125rem;">{{ TeacherHonorarium::rupiah($periodData['total_amount']) }}</strong>
                        @else
                            <span class="admin-muted">Belum tersedia</span>
                        @endif
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Total yang Sudah Dibayar</dt>
                    <dd style="color: #057A46;">
                        <strong>{{ TeacherHonorarium::rupiah($periodData['paid_amount']) }}</strong>
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Sisa Pembayaran</dt>
                    <dd>
                        @if ($periodData['is_configured'])
                            @if ($periodData['remaining_amount'] > 0)
                                <strong style="color: #92400e;">{{ TeacherHonorarium::rupiah($periodData['remaining_amount']) }}</strong>
                            @else
                                <span class="admin-badge is-success">Lunas</span>
                            @endif
                        @else
                            <span class="admin-muted">Belum tersedia</span>
                        @endif
                    </dd>
                </div>
                <div class="admin-detail-row">
                    <dt>Status Pembayaran</dt>
                    <dd>
                        @if ($periodData['status'])
                            <span class="admin-badge {{ $periodData['status']->badgeClass() }}">
                                {{ $periodData['status']->label() }}
                            </span>
                        @else
                            <span class="admin-badge is-neutral">Belum Ditetapkan</span>
                        @endif
                    </dd>
                </div>
                @if ($periodData['notes'])
                    <div class="admin-detail-row">
                        <dt>Catatan Penetapan</dt>
                        <dd>{{ $periodData['notes'] }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </section>

    {{-- Riwayat Transaksi Pembayaran Periode Ini --}}
    <section class="teacher-section" aria-labelledby="transactions-heading">
        <div class="teacher-section-head">
            <h2 id="transactions-heading">Riwayat Transaksi Pembayaran ({{ $periodData['label'] }})</h2>
        </div>

        @if ($periodData['payments']->isEmpty())
            <div class="teacher-honor-card" style="padding: 1.5rem; text-align: center; color: var(--admin-muted);">
                Belum ada transaksi pembayaran yang dicatat oleh Admin untuk periode {{ $periodData['label'] }}.
            </div>
        @else
            <div class="teacher-honor-card" style="padding: 0; overflow: hidden;">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tanggal Pembayaran</th>
                                <th class="admin-table-num">Nominal Transfer</th>
                                <th>Metode Pembayaran</th>
                                <th>Nomor Referensi</th>
                                <th>Bukti Pembayaran</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($periodData['payments'] as $payment)
                                <tr>
                                    <td>{{ $payment->paid_on?->translatedFormat('d F Y') ?? '-' }}</td>
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
                                                Lihat Bukti
                                            </a>
                                        @else
                                            <span class="admin-muted">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td>{{ $payment->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>

    {{-- Riwayat Honorarium Seluruh Periode --}}
    <section class="teacher-section" aria-labelledby="history-heading">
        <div class="teacher-section-head">
            <h2 id="history-heading">Daftar Periode Honorarium Lainnya</h2>
        </div>

        @if ($history->isEmpty())
            <div class="teacher-sessions">
                <x-admin.empty-state title="Belum ada riwayat honorarium" icon="payments">
                    Catatan honor mengajar resmi akan tampil di sini setelah ditetapkan oleh Admin.
                </x-admin.empty-state>
            </div>
        @else
            <div class="teacher-honor-card" style="padding: 0; overflow: hidden;">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th class="admin-table-num">Sesi</th>
                                <th class="admin-table-num">Total Honor</th>
                                <th class="admin-table-num">Sudah Dibayar</th>
                                <th class="admin-table-num">Sisa</th>
                                <th>Status</th>
                                <th class="admin-table-action"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->periodLabel() }}</strong>
                                    </td>
                                    <td class="admin-table-num">{{ $item->session_count }} sesi</td>
                                    <td class="admin-table-num">{{ TeacherHonorarium::rupiah($item->amount) }}</td>
                                    <td class="admin-table-num" style="color: #057A46;">{{ TeacherHonorarium::rupiah($item->paid_amount) }}</td>
                                    <td class="admin-table-num">
                                        @if ($item->remainingAmount() > 0)
                                            <span style="color: #92400e; font-weight: 700;">{{ TeacherHonorarium::rupiah($item->remainingAmount()) }}</span>
                                        @else
                                            <span class="admin-muted">Rp0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="admin-badge {{ $item->status->badgeClass() }}">
                                            {{ $item->status->label() }}
                                        </span>
                                    </td>
                                    <td class="admin-table-action" style="white-space: nowrap;">
                                        <a href="{{ route('teacher.honorariums.index', ['bulan' => $item->period_month, 'tahun' => $item->period_year]) }}"
                                            class="admin-btn admin-btn-secondary admin-btn-sm">Pilih</a>
                                        <a href="{{ route('teacher.honorariums.show', $item) }}"
                                            class="admin-btn admin-btn-ghost admin-btn-sm">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($history->hasPages())
                <div style="margin-top: 1rem;">
                    {{ $history->links('admin.partials.pagination') }}
                </div>
            @endif
        @endif
    </section>
@endsection
