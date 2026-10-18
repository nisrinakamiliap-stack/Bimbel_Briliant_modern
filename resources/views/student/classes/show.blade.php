@extends('layouts.student')

@section('title', $classroom->name . ' — Portal Murid')

@section('content')
<div class="st-container st-page">

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" style="margin-bottom:1.25rem;font-size:.875rem;color:var(--st-muted);">
        <a href="{{ route('student.classes.index') }}" style="color:var(--st-primary);text-decoration:none;">Kelas Saya</a>
        <span style="margin:0 .5rem;">›</span>
        <span>{{ $classroom->name }}</span>
    </nav>

    {{-- Header kelas --}}
    <div class="st-detail-card" style="margin-bottom:1.5rem;">
        <div class="st-detail-header">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                <div>
                    <h1 class="st-detail-title">{{ $classroom->name }}</h1>
                    <p class="st-detail-meta">
                        <span>{{ $classroom->program->name }}</span>
                        @if ($classroom->code)
                            <span class="st-detail-meta-sep">·</span>
                            <span>Kode: {{ $classroom->code }}</span>
                        @endif
                        <span class="st-detail-meta-sep">·</span>
                        <span class="st-status {{ $classroom->is_active ? 'is-success' : 'is-neutral' }}">{{ $classroom->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="st-detail-body">
            <div class="st-grid-2">
                <div class="st-info-list">
                    <div class="st-info-row">
                        <span class="st-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                        <div>
                            <p class="st-info-label">Guru Pengajar</p>
                            <p class="st-info-value">{{ $classroom->teacher?->name ?? 'Belum ditentukan' }}</p>
                        </div>
                    </div>
                    <div class="st-info-row">
                        <span class="st-info-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/></svg></span>
                        <div>
                            <p class="st-info-label">Mode Belajar</p>
                            <p class="st-info-value">{{ $classroom->learning_mode->label() }}</p>
                        </div>
                    </div>
                </div>
                <div class="st-info-list">
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
                </div>
            </div>
            @if ($classroom->description)
                <p style="margin-top:1rem;font-size:.9375rem;color:var(--st-muted);line-height:1.7;">{{ $classroom->description }}</p>
            @endif
        </div>
    </div>

    {{-- Materi --}}
    <section aria-labelledby="mat-heading">
        <div class="st-section-header">
            <h2 class="st-section-title" id="mat-heading" style="margin:0;">Materi Pembelajaran</h2>
        </div>
        @forelse ($materials as $material)
            <a href="{{ route('student.learning.materials.show', $material) }}" class="st-card-link">
                <p class="st-card-title">{{ $material->title }}</p>
                <p class="st-card-meta">{{ $material->published_at?->translatedFormat('d M Y') }}</p>
            </a>
        @empty
            <div class="st-empty">
                <p class="st-empty-title">Belum ada materi</p>
                <p class="st-empty-desc">Materi akan muncul di sini setelah guru menerbitkannya.</p>
            </div>
        @endforelse
    </section>
</div>
@endsection
