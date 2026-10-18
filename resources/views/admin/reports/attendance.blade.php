@extends('layouts.admin')

@section('title', 'Laporan Absensi')
@section('page_title', 'Laporan')

@section('report_filters')
    <div style="flex: 1; min-width: 10rem;">
        <label for="kelas" class="admin-label">Kelas</label>
        <select id="kelas" name="kelas" class="admin-select">
            <option value="">Semua kelas</option>
            @foreach ($classrooms as $classroomOption)
                <option value="{{ $classroomOption->id }}" @selected((string) ($filters['kelas'] ?? '') === (string) $classroomOption->id)>{{ $classroomOption->name }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex: 1; min-width: 10rem;">
        <label for="murid" class="admin-label">Murid</label>
        <select id="murid" name="murid" class="admin-select">
            <option value="">Semua murid</option>
            @foreach ($students as $studentOption)
                <option value="{{ $studentOption->id }}" @selected((string) ($filters['murid'] ?? '') === (string) $studentOption->id)>{{ $studentOption->name }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex: 1; min-width: 10rem;">
        <label for="guru" class="admin-label">Guru</label>
        <select id="guru" name="guru" class="admin-select">
            <option value="">Semua guru</option>
            @foreach ($teachers as $teacherOption)
                <option value="{{ $teacherOption->id }}" @selected((string) ($filters['guru'] ?? '') === (string) $teacherOption->id)>{{ $teacherOption->name }}</option>
            @endforeach
        </select>
    </div>
@endsection

@section('content')
    @include('admin.reports.partials.header', [
        'title' => 'Laporan Absensi',
        'description' => 'Hanya sesi yang sudah dicatat yang dihitung; jadwal yang belum berlangsung tidak dianggap alpa.',
        'route' => 'admin.reports.attendance',
    ])

    <div class="admin-grid">
        <section class="admin-panel">
            <div class="admin-panel-header"><div><h3>Kehadiran Murid</h3><p>Jumlah catatan per status.</p></div></div>
            @include('admin.reports.partials.bars', ['rows' => $studentCounts, 'empty' => 'Belum ada catatan absensi murid pada periode ini.'])
        </section>
        <section class="admin-panel">
            <div class="admin-panel-header"><div><h3>Kehadiran Guru</h3><p>Jumlah check-in per status.</p></div></div>
            @include('admin.reports.partials.bars', ['rows' => $teacherCounts, 'empty' => 'Belum ada catatan absensi guru pada periode ini.'])
        </section>

        <section class="admin-panel is-wide">
            <div class="admin-panel-header"><div><h3>Rekap per Murid</h3><p>Data ekspor CSV mengikuti tabel ini.</p></div></div>
            @if ($studentRecap->isEmpty())
                <p class="admin-result-info">Belum ada catatan absensi murid pada periode ini.</p>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table" id="report-student-recap">
                        <thead><tr><th>Murid</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>Total Sesi</th><th>% Hadir</th></tr></thead>
                        <tbody>
                            @foreach ($studentRecap as $row)
                                <tr>
                                    <td class="admin-cell-name">{{ $row['name'] }}</td>
                                    <td>{{ $row['counts']['present'] }}</td>
                                    <td>{{ $row['counts']['excused'] }}</td>
                                    <td>{{ $row['counts']['sick'] }}</td>
                                    <td>{{ $row['counts']['absent'] }}</td>
                                    <td>{{ $row['total'] }}</td>
                                    <td>{{ $row['total'] > 0 ? round($row['counts']['present'] / $row['total'] * 100) : 0 }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-panel is-wide">
            <div class="admin-panel-header"><div><h3>Rekap per Guru</h3><p>Sesi mengajar yang di-check-in.</p></div></div>
            @if ($teacherRecap->isEmpty())
                <p class="admin-result-info">Belum ada catatan absensi guru pada periode ini.</p>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Guru</th><th>Sesi</th><th>Terlambat</th><th>Total Durasi</th></tr></thead>
                        <tbody>
                            @foreach ($teacherRecap as $row)
                                <tr>
                                    <td class="admin-cell-name">{{ $row['name'] }}</td>
                                    <td>{{ $row['sessions'] }}</td>
                                    <td>{{ $row['late'] }}</td>
                                    <td>{{ intdiv($row['minutes'], 60) }} jam {{ $row['minutes'] % 60 }} menit</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
