@extends('layouts.admin')

@section('title', 'Laporan Murid & Pendaftaran')
@section('page_title', 'Laporan')

@section('report_filters')
    <div style="flex: 1; min-width: 12rem;">
        <label for="program" class="admin-label">Program</label>
        <select id="program" name="program" class="admin-select">
            <option value="">Semua program</option>
            @foreach ($programs as $programOption)
                <option value="{{ $programOption->id }}" @selected((string) ($filters['program'] ?? '') === (string) $programOption->id)>{{ $programOption->name }}</option>
            @endforeach
        </select>
    </div>
@endsection

@section('content')
    @include('admin.reports.partials.header', [
        'title' => 'Laporan Murid & Pendaftaran',
        'description' => 'Jumlah murid, status pendaftaran, dan sebaran murid aktif.',
        'route' => 'admin.reports.students',
    ])

    <section class="admin-stats" aria-label="Ringkasan murid">
        <x-admin.stat-card id="report-total-students" label="Total Murid" :value="$totalStudents" icon="students" tone="green" note="Akun murid terdaftar" />
        <x-admin.stat-card id="report-active-students" label="Murid Aktif" :value="$activeStudents" icon="students" tone="navy" note="Memiliki pendaftaran Diterima" />
        <x-admin.stat-card id="report-enrollments" label="Pendaftaran pada Periode" :value="$enrollmentCount" icon="enrollments" tone="amber" note="Berdasarkan tanggal daftar" />
    </section>

    <div class="admin-grid">
        <section class="admin-panel">
            <div class="admin-panel-header"><div><h3>Pendaftaran per Status</h3><p>Pada periode terpilih.</p></div></div>
            @include('admin.reports.partials.bars', ['rows' => $statusCounts, 'empty' => 'Belum ada pendaftaran pada periode ini.'])
        </section>
        <section class="admin-panel">
            <div class="admin-panel-header"><div><h3>Pendaftaran per Bulan</h3><p>Pada periode terpilih.</p></div></div>
            @include('admin.reports.partials.bars', ['rows' => $monthly->pluck('count', 'label'), 'empty' => 'Belum ada pendaftaran pada periode ini.'])
        </section>
        <section class="admin-panel">
            <div class="admin-panel-header"><div><h3>Murid Aktif per Program</h3><p>Pendaftaran aktif saat ini.</p></div></div>
            @include('admin.reports.partials.bars', ['rows' => $byProgram, 'empty' => 'Belum ada pendaftaran aktif.'])
        </section>
        <section class="admin-panel">
            <div class="admin-panel-header"><div><h3>Murid Aktif per Kelas</h3><p>Pendaftaran aktif yang sudah ditempatkan.</p></div></div>
            @include('admin.reports.partials.bars', ['rows' => $byClass, 'empty' => 'Belum ada murid yang ditempatkan di kelas.'])
        </section>
    </div>
@endsection
