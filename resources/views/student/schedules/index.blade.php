@extends('layouts.student')

@section('title', 'Jadwal Belajar — Portal Murid')

@section('content')
<div class="st-container st-page">
    <div class="st-page-header">
        <h1 class="st-page-title">Jadwal Belajar</h1>
        <p class="st-page-sub">Jadwal pertemuan dari kelas yang Anda ikuti.</p>
    </div>

    @if (session('success'))
        <div class="st-flash st-flash--success" role="status">{{ session('success') }}</div>
    @endif

    @unless ($hasClasses)
        <div class="st-empty">
            <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <p class="st-empty-title">Belum ada jadwal</p>
            <p class="st-empty-desc">Jadwal akan tampil setelah pendaftaran Anda diterima dan kelas ditentukan.</p>
        </div>
    @else
        {{-- Filter --}}
        @if ($myClassrooms->count() > 1)
        <form method="GET" action="{{ route('student.schedules.index') }}" style="margin-bottom:1.5rem;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
            <label for="kelas" class="st-label" style="margin:0;white-space:nowrap;">Filter Kelas:</label>
            <select id="kelas" name="kelas" class="st-select" style="max-width:18rem;" onchange="this.form.submit()">
                <option value="0">Semua Kelas</option>
                @foreach ($myClassrooms as $cls)
                    <option value="{{ $cls->id }}" @selected($classroomFilter === $cls->id)>{{ $cls->name }} — {{ $cls->program->name }}</option>
                @endforeach
            </select>
        </form>
        @endif

        {{-- Pertemuan terdekat --}}
        <section aria-labelledby="upcoming-heading" style="margin-bottom:2rem;">
            <h2 class="st-section-title" id="upcoming-heading">Pertemuan Mendatang</h2>
            @forelse ($upcomingOccurrences as $item)
                @php $schedule = $item['schedule']; $nextAt = $item['next_at']; @endphp
                <div class="st-schedule-card">
                    <div class="st-schedule-time">
                        <span class="st-schedule-time-main">{{ $nextAt->format('H:i') }}</span>
                        <span class="st-schedule-time-day">{{ $nextAt->translatedFormat('D, d M') }}</span>
                    </div>
                    <div class="st-schedule-body">
                        <p class="st-schedule-name">{{ $schedule->classroom->name }}</p>
                        <p class="st-schedule-detail">
                            {{ $schedule->classroom->program->name }}
                            @if ($schedule->classroom->teacher) · {{ $schedule->classroom->teacher->name }} @endif
                            · {{ $schedule->timeRange() }}
                            @if ($schedule->room) · Ruang {{ $schedule->room }} @endif
                        </p>
                    </div>
                    <div style="flex-shrink:0;text-align:right;">
                        @if ($nextAt->isToday())
                            <span class="st-status is-success">Hari Ini</span>
                        @elseif ($nextAt->isTomorrow())
                            <span class="st-status is-info">Besok</span>
                        @endif
                        <span class="st-status is-neutral" style="display:block;margin-top:.25rem;">{{ $schedule->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                </div>
            @empty
                <p style="color:var(--st-muted);font-size:.9rem;">Tidak ada pertemuan mendatang dalam waktu dekat.</p>
            @endforelse
        </section>

        {{-- Semua jadwal --}}
        <section aria-labelledby="all-schedule-heading">
            <h2 class="st-section-title" id="all-schedule-heading">Semua Jadwal Mingguan</h2>
            <div class="st-table-wrap">
                <table class="st-table">
                    <thead>
                        <tr>
                            <th>Hari & Jam</th>
                            <th>Kelas</th>
                            <th>Program</th>
                            <th>Guru</th>
                            <th>Ruangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allSchedules as $schedule)
                            <tr>
                                <td>
                                    <strong>{{ $schedule->day_of_week->label() }}</strong><br>
                                    <span class="is-muted">{{ $schedule->timeRange() }}</span>
                                </td>
                                <td>{{ $schedule->classroom->name }}</td>
                                <td>{{ $schedule->classroom->program->name }}</td>
                                <td>{{ $schedule->classroom->teacher?->name ?? '—' }}</td>
                                <td>{{ $schedule->room ?? '—' }}</td>
                                <td>
                                    <span class="st-status {{ $schedule->is_active ? 'is-success' : 'is-neutral' }}">
                                        {{ $schedule->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center;color:var(--st-muted);padding:2rem;">Tidak ada jadwal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endunless
</div>
@endsection
