@extends('layouts.student')

@section('title', 'Beranda — Portal Murid')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
    $muridImg = file_exists(public_path('images/murid.png')) ? asset('images/murid.png') : null;
@endphp

{{-- ── HERO ── --}}
<section class="st-hero st-container" aria-labelledby="hero-heading">
    {{-- Ilustrasi sebagai background visual kanan --}}
    @if ($muridImg)
    <div class="st-hero-img-wrap" aria-hidden="true">
        <img src="{{ $muridImg }}" alt="" class="st-hero-img">
    </div>
    @endif

    <div class="st-hero-inner">
        <p class="st-hero-greeting">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            {{ $greeting }}
        </p>
        <h1 class="st-hero-title" id="hero-heading">
            Halo, <span>{{ $student->name }}</span>!
        </h1>
        @if ($hasClasses)
            <a href="{{ route('student.schedules.index') }}" class="st-btn st-btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Lihat Jadwal Belajar
            </a>
        @else
            <p style="margin:0;padding:.875rem 1rem;background:rgba(185,246,208,.5);border:1px solid var(--st-border);border-radius:.75rem;font-size:.875rem;color:var(--st-muted);">
                Anda belum memiliki kelas aktif. Silakan tunggu konfirmasi pendaftaran dari admin.
            </p>
        @endif
    </div>
</section>

{{-- ── AKSES CEPAT ── --}}
<div class="st-container">
    <section class="st-quick-access" aria-labelledby="quick-heading">
        <h2 class="st-section-title" id="quick-heading">Akses Cepat</h2>
        <nav class="st-quick-grid" aria-label="Navigasi cepat">
            <a href="{{ route('student.schedules.index') }}" class="st-quick-item">
                <span class="st-quick-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></span>
                <span class="st-quick-label">Jadwal Belajar</span>
            </a>
            <a href="{{ route('student.classes.index') }}" class="st-quick-item">
                <span class="st-quick-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></span>
                <span class="st-quick-label">Kelas Saya</span>
            </a>
            <a href="{{ route('student.materials.index') }}" class="st-quick-item">
                <span class="st-quick-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></span>
                <span class="st-quick-label">Materi</span>
            </a>
            <a href="{{ route('student.payments.index') }}" class="st-quick-item">
                <span class="st-quick-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
                <span class="st-quick-label">Pembayaran</span>
            </a>
        </nav>
    </section>

    {{-- ── JADWAL TERDEKAT ── --}}
    <section class="st-section" aria-labelledby="schedule-heading">
        <div class="st-section-header">
            <h2 class="st-section-title" id="schedule-heading" style="margin:0;">Jadwal Belajar Terdekat</h2>
            <a href="{{ route('student.schedules.index') }}" class="st-view-all">Lihat Semua →</a>
        </div>

        @forelse ($upcomingSchedules as $item)
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
                        @if ($schedule->classroom->teacher)
                            · {{ $schedule->classroom->teacher->name }}
                        @endif
                        @if ($schedule->room)
                            · Ruang {{ $schedule->room }}
                        @endif
                        · {{ $schedule->timeRange() }}
                    </p>
                </div>
                @if ($nextAt->isToday())
                    <span class="st-status is-success">Hari Ini</span>
                @elseif ($nextAt->isTomorrow())
                    <span class="st-status is-info">Besok</span>
                @endif
            </div>
        @empty
            <div class="st-empty">
                <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                <p class="st-empty-title">Belum ada jadwal</p>
                <p class="st-empty-desc">Jadwal belajar akan tampil setelah Anda terdaftar di kelas aktif.</p>
            </div>
        @endforelse
    </section>

    {{-- ── MATERI TERBARU ── --}}
    @if ($recentMaterials->isNotEmpty())
    <section class="st-section" aria-labelledby="material-heading">
        <div class="st-section-header">
            <h2 class="st-section-title" id="material-heading" style="margin:0;">Materi Terbaru</h2>
            <a href="{{ route('student.materials.index') }}" class="st-view-all">Lihat Semua →</a>
        </div>
        @foreach ($recentMaterials as $material)
            <a href="{{ route('student.learning.materials.show', $material) }}" class="st-card-link">
                <p class="st-card-title">{{ $material->title }}</p>
                <p class="st-card-meta">{{ $material->classroom->name }} · {{ $material->published_at?->translatedFormat('d M Y') }}</p>
            </a>
        @endforeach
    </section>
    @endif

</div>
@endsection
