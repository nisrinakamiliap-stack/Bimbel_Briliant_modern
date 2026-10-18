@extends('layouts.admin')

@section('title', 'Laporan Pembelajaran')
@section('page_title', 'Laporan')

@section('report_filters')
    <div style="flex: 1; min-width: 12rem;">
        <label for="kelas" class="admin-label">Kelas</label>
        <select id="kelas" name="kelas" class="admin-select">
            <option value="">Semua kelas</option>
            @foreach ($classrooms as $classroomOption)
                <option value="{{ $classroomOption->id }}" @selected((string) ($filters['kelas'] ?? '') === (string) $classroomOption->id)>{{ $classroomOption->name }}</option>
            @endforeach
        </select>
    </div>
@endsection

@section('content')
    @include('admin.reports.partials.header', [
        'title' => 'Laporan Pembelajaran',
        'description' => 'Tugas terbit dengan tenggat pada periode terpilih, pengumpulan, dan rekap nilai.',
        'route' => 'admin.reports.learning',
    ])

    <section class="admin-stats" aria-label="Ringkasan pembelajaran">
        <x-admin.stat-card id="report-materials" label="Materi Terbit" :value="$totals['materials']" icon="programs" tone="green" note="Seluruh periode" />
        <x-admin.stat-card id="report-assignments" label="Tugas pada Periode" :value="$totals['assignments']" icon="enrollments" tone="navy" note="Berdasarkan tenggat" />
        <x-admin.stat-card id="report-submissions" label="Pengumpulan" :value="$totals['submissions']" icon="check" tone="amber" note="Dikirim pada periode" />
        <x-admin.stat-card id="report-missing" label="Belum Dikumpulkan" :value="$totals['missing']" icon="alert" tone="sky" note="Tugas yang tenggatnya sudah lewat" />
    </section>

    <section class="admin-panel">
        <div class="admin-panel-header"><div><h3>Rekap per Tugas</h3><p>{{ $totals['graded'] }} pengumpulan dinilai · {{ $totals['ungraded'] }} belum dinilai. Data ekspor CSV mengikuti tabel ini.</p></div></div>
        @if ($rows->isEmpty())
            <p class="admin-result-info">Belum ada tugas terbit dengan tenggat pada periode ini.</p>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table" id="report-learning-table">
                    <thead><tr><th>Tugas</th><th>Kelas</th><th>Tenggat</th><th>Peserta</th><th>Mengumpulkan</th><th>Belum</th><th>Terlambat</th><th>Dinilai</th><th>Rata-rata</th></tr></thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="admin-cell-name">{{ $row['title'] }}</td>
                                <td>{{ $row['classroom'] }}</td>
                                <td>{{ $row['due_at']->translatedFormat('d M Y, H:i') }}</td>
                                <td>{{ $row['participants'] }}</td>
                                <td>{{ $row['submitted'] }}</td>
                                <td>{{ $row['missing'] ?? 'Belum lewat tenggat' }}</td>
                                <td>{{ $row['late'] }}</td>
                                <td>{{ $row['graded'] }}</td>
                                <td>{{ $row['average'] === null ? '—' : str_replace('.', ',', (string) $row['average']).' / '.$row['max_score'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
