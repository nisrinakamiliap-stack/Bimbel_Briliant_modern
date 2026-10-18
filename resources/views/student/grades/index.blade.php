@extends('layouts.student')

@section('title', 'Nilai Saya — Portal Murid')

@section('content')
<div class="st-container st-page">
    <div class="st-page-header">
        <h1 class="st-page-title">Nilai Saya</h1>
        <p class="st-page-sub">Hasil penilaian tugas yang sudah diperiksa oleh guru.</p>
    </div>

    @unless ($hasClasses)
        <div class="st-empty">
            <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
            <p class="st-empty-title">Belum ada kelas aktif</p>
            <p class="st-empty-desc">Nilai akan tampil setelah Anda terdaftar di kelas dan tugas dinilai.</p>
        </div>
    @else
        @forelse ($grades as $grade)
            @php
                $submission = $grade->submission;
                $assignment = $submission->assignment;
                $classroom  = $assignment->classroom;
            @endphp
            <div class="st-card" style="margin-bottom:.75rem;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:0;">
                        <h2 style="margin:0 0 .25rem;font-size:1rem;font-weight:700;color:var(--st-forest);">{{ $assignment->title }}</h2>
                        <p style="margin:0;font-size:.8125rem;color:var(--st-muted);">
                            {{ $classroom->name }}
                            @if ($classroom->teacher) · {{ $classroom->teacher->name }} @endif
                            · Dinilai {{ $grade->graded_at?->translatedFormat('d M Y') ?? '—' }}
                        </p>
                        @if ($grade->feedback)
                            <blockquote style="margin:.75rem 0 0;padding:.625rem 1rem;background:var(--st-mint-bg);border-left:3px solid var(--st-primary);border-radius:0 .5rem .5rem 0;font-size:.875rem;color:var(--st-muted);font-style:italic;">
                                "{{ $grade->feedback }}"
                            </blockquote>
                        @endif
                    </div>
                    <div style="flex-shrink:0;text-align:center;">
                        <span style="display:block;font-size:1.875rem;font-weight:800;color:var(--st-primary);">{{ $grade->formattedScore() }}</span>
                        <span style="font-size:.75rem;color:var(--st-muted);">/ {{ $assignment->max_score }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="st-empty">
                <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
                <p class="st-empty-title">Belum ada nilai</p>
                <p class="st-empty-desc">Nilai akan tampil setelah tugas dikumpulkan dan dinilai oleh guru.</p>
            </div>
        @endforelse

        <div class="st-pagination">{{ $grades->links() }}</div>
    @endunless
</div>
@endsection
