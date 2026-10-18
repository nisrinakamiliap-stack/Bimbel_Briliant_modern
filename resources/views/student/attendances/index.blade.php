@extends('layouts.student')

@section('title', 'Absensi Saya — Portal Murid')

@section('content')
<div class="st-container st-page">
    <div class="st-page-header">
        <h1 class="st-page-title">Absensi Saya</h1>
        <p class="st-page-sub">Riwayat kehadiran Anda di setiap sesi belajar.</p>
    </div>

    {{-- Ringkasan --}}
    @if (array_sum($counts) > 0)
    <div class="st-stat-grid" style="margin-bottom:2rem;">
        <div class="st-stat-box">
            <p class="st-stat-number is-success">{{ $counts['hadir'] }}</p>
            <p class="st-stat-label">Hadir</p>
        </div>
        <div class="st-stat-box">
            <p class="st-stat-number is-pending">{{ $counts['izin'] }}</p>
            <p class="st-stat-label">Izin</p>
        </div>
        <div class="st-stat-box">
            <p class="st-stat-number is-pending">{{ $counts['sakit'] }}</p>
            <p class="st-stat-label">Sakit</p>
        </div>
        <div class="st-stat-box">
            <p class="st-stat-number is-danger">{{ $counts['alpa'] }}</p>
            <p class="st-stat-label">Alpa</p>
        </div>
    </div>
    @endif

    {{-- Tabel absensi --}}
    @if ($attendances->isEmpty())
        <div class="st-empty">
            <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
            <p class="st-empty-title">Belum ada catatan absensi</p>
            <p class="st-empty-desc">Absensi akan dicatat oleh guru atau admin saat sesi berlangsung.</p>
        </div>
    @else
        <div class="st-table-wrap">
            <table class="st-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kelas</th>
                        <th>Sesi</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($attendance->session_date)->translatedFormat('d M Y') }}</strong><br>
                                <span class="is-muted">{{ \Carbon\Carbon::parse($attendance->session_date)->translatedFormat('l') }}</span>
                            </td>
                            <td>{{ $attendance->schedule?->classroom?->name ?? '—' }}</td>
                            <td>{{ $attendance->schedule?->timeRange() ?? '—' }}</td>
                            <td><span class="st-status {{ $attendance->status->badgeClass() }}">{{ $attendance->status->label() }}</span></td>
                            <td>{{ $attendance->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="st-pagination">{{ $attendances->links() }}</div>
    @endif
</div>
@endsection
