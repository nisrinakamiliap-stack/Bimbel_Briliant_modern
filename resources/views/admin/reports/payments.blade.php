@extends('layouts.admin')

@section('title', 'Laporan Pembayaran')
@section('page_title', 'Laporan')

@use('App\Models\Payment')

@section('report_filters')
    <div style="flex: 1; min-width: 12rem;">
        <label for="metode" class="admin-label">Metode</label>
        <select id="metode" name="metode" class="admin-select">
            <option value="">Semua metode</option>
            @foreach ($methods as $methodOption)
                <option value="{{ $methodOption->value }}" @selected(($filters['metode'] ?? null) === $methodOption->value)>{{ $methodOption->label() }}</option>
            @endforeach
        </select>
    </div>
@endsection

@section('content')
    @include('admin.reports.partials.header', [
        'title' => 'Laporan Pembayaran',
        'description' => 'Hanya transaksi Berhasil (terverifikasi) yang dihitung sebagai pendapatan diterima.',
        'route' => 'admin.reports.payments',
    ])

    <section class="admin-stats" aria-label="Ringkasan pembayaran">
        @foreach ([
            'paid' => ['Pembayaran Berhasil', 'is-green', 'check', 'Pendapatan diterima'],
            'waiting' => ['Menunggu Verifikasi', 'is-amber', 'clock', 'Belum dihitung sebagai pendapatan'],
            'rejected' => ['Ditolak', 'is-navy', 'alert', 'Tidak dihitung'],
            'outstanding' => ['Tagihan Belum Lunas', 'is-sky', 'payments', 'Saat ini (tidak bergantung periode)'],
        ] as $key => [$label, $tone, $icon, $note])
            <article class="admin-stat-card" id="report-{{ $key }}">
                <div class="admin-stat-icon {{ $tone }}"><x-admin.icon :name="$icon" :size="22" /></div>
                <div>
                    <p class="admin-stat-label">{{ $label }}</p>
                    <p class="admin-stat-value" style="font-size: 1.375rem;">{{ Payment::rupiah($summary[$key]['total']) }}</p>
                    <p class="admin-stat-note">{{ $summary[$key]['count'] }} {{ $key === 'outstanding' ? 'pendaftaran' : 'transaksi' }} · {{ $note }}</p>
                </div>
            </article>
        @endforeach
    </section>

    <div class="admin-grid">
        <section class="admin-panel">
            <div class="admin-panel-header"><div><h3>Pembayaran Berhasil per Bulan</h3><p>Berdasarkan tanggal pembayaran.</p></div></div>
            @include('admin.reports.partials.bars', ['rows' => $monthlyPaid->pluck('total', 'label'), 'empty' => 'Belum ada pembayaran berhasil pada periode ini.', 'isMoney' => true])
        </section>
        <section class="admin-panel">
            <div class="admin-panel-header"><div><h3>Pembayaran Berhasil per Metode</h3><p>Pada periode terpilih.</p></div></div>
            @include('admin.reports.partials.bars', ['rows' => $byMethod, 'empty' => 'Belum ada pembayaran berhasil pada periode ini.', 'isMoney' => true])
        </section>
    </div>
@endsection
