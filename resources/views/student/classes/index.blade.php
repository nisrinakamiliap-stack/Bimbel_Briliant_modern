@extends('layouts.student')

@section('title', 'Kelas Saya — Portal Murid')

@section('content')
<div class="st-container st-page">
    <div class="st-page-header">
        <h1 class="st-page-title">Kelas Saya</h1>
        <p class="st-page-sub">Daftar kelas yang sedang Anda ikuti.</p>
    </div>

    @unless ($hasClasses)
        <div class="st-empty">
            <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
            <p class="st-empty-title">Belum memiliki kelas aktif</p>
            <p class="st-empty-desc">Anda akan ditempatkan di kelas setelah pendaftaran diterima dan admin menetapkan kelas.</p>
        </div>
    @else
        <div class="st-grid-2">
            @foreach ($classrooms as $classroom)
                <article class="st-detail-card" style="display:flex;flex-direction:column;">
                    <div class="st-detail-header">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                            <div>
                                <h2 class="st-detail-title" style="font-size:1.125rem;">{{ $classroom->name }}</h2>
                                <p style="margin:.25rem 0 0;font-size:.875rem;color:var(--st-muted);">{{ $classroom->program->name }}</p>
                            </div>
                            <span class="st-status {{ $classroom->is_active ? 'is-success' : 'is-neutral' }}">{{ $classroom->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                        </div>
                    </div>
                    <div class="st-detail-body" style="flex:1;">
                        <div class="st-info-list">
                            <div class="st-info-row">
                                <span class="st-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                                <div>
                                    <p class="st-info-label">Guru Pengajar</p>
                                    <p class="st-info-value">{{ $classroom->teacher?->name ?? 'Belum ditentukan' }}</p>
                                </div>
                            </div>
                            <div class="st-info-row">
                                <span class="st-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></span>
                                <div>
                                    <p class="st-info-label">Jadwal</p>
                                    <p class="st-info-value">
                                        @forelse ($classroom->schedules as $schedule)
                                            {{ $schedule->summary() }}<br>
                                        @empty
                                            Belum ada jadwal
                                        @endforelse
                                    </p>
                                </div>
                            </div>
                            <div class="st-info-row">
                                <span class="st-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                                <div>
                                    <p class="st-info-label">Mode Belajar</p>
                                    <p class="st-info-value">{{ $classroom->learning_mode->label() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="padding:1rem 1.25rem;border-top:1px solid var(--st-border);">
                        <a href="{{ route('student.classes.show', $classroom) }}" class="st-btn st-btn-primary st-btn-sm st-btn-full">
                            Lihat Detail Kelas →
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endunless
</div>
@endsection
