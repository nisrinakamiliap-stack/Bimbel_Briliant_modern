@extends('layouts.student')

@section('title', 'Tugas Saya — Portal Murid')

@section('content')
<div class="st-container st-page">
    <div class="st-page-header">
        <h1 class="st-page-title">Tugas Saya</h1>
        <p class="st-page-sub">Daftar tugas dari kelas yang Anda ikuti, diurutkan berdasarkan tenggat waktu.</p>
    </div>

    @if (session('success'))
        <div class="st-flash st-flash--success" role="status">{{ session('success') }}</div>
    @endif

    @unless ($hasClasses)
        <div class="st-empty">
            <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></div>
            <p class="st-empty-title">Belum ada kelas aktif</p>
            <p class="st-empty-desc">Tugas akan tampil setelah Anda terdaftar di kelas.</p>
        </div>
    @else
        @if ($myClassrooms->count() > 1)
        <form method="GET" action="{{ route('student.assignments.index') }}" style="margin-bottom:1.5rem;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
            <label for="kelas" class="st-label" style="margin:0;white-space:nowrap;">Filter Kelas:</label>
            <select id="kelas" name="kelas" class="st-select" style="max-width:18rem;" onchange="this.form.submit()">
                <option value="0">Semua Kelas</option>
                @foreach ($myClassrooms as $cls)
                    <option value="{{ $cls->id }}" @selected($classroomFilter === $cls->id)>{{ $cls->name }}</option>
                @endforeach
            </select>
        </form>
        @endif

        @forelse ($assignments as $assignment)
            @php
                $mySubmission = $assignment->submissions->first();
                $isPastDue = $assignment->isPastDue(now());
                $hasGrade = $mySubmission?->grade !== null;
            @endphp
            <div class="st-card" style="margin-bottom:.75rem;display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:flex-start;gap:.75rem;flex-wrap:wrap;margin-bottom:.5rem;">
                        <h2 style="margin:0;font-size:1rem;font-weight:700;color:var(--st-forest);flex:1;">{{ $assignment->title }}</h2>
                        @if ($hasGrade)
                            <span class="st-status is-success">Sudah Dinilai · {{ $mySubmission->grade->formattedScore() }}/{{ $assignment->max_score }}</span>
                        @elseif ($mySubmission)
                            <span class="st-status is-pending">{{ $mySubmission->statusLabel() }}</span>
                        @elseif ($isPastDue && !$assignment->allow_late_submission)
                            <span class="st-status is-danger">Tenggat Lewat</span>
                        @else
                            <span class="st-status is-neutral">Belum Dikumpulkan</span>
                        @endif
                    </div>
                    <p style="margin:0;font-size:.8125rem;color:var(--st-muted);">
                        {{ $assignment->classroom->name }}
                        · Tenggat: <strong style="color:{{ $isPastDue ? '#dc2626' : 'var(--st-forest)' }}">{{ $assignment->due_at->translatedFormat('d M Y, H:i') }}</strong>
                        @if ($assignment->allow_late_submission) · <em>Terlambat diizinkan</em> @endif
                    </p>
                </div>
                <a href="{{ route('student.learning.assignments.show', $assignment) }}"
                   class="st-btn {{ $mySubmission ? 'st-btn-secondary' : 'st-btn-primary' }} st-btn-sm"
                   style="flex-shrink:0;">
                    {{ $mySubmission ? 'Lihat Detail' : 'Kumpulkan' }}
                </a>
            </div>
        @empty
            <div class="st-empty">
                <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg></div>
                <p class="st-empty-title">Tidak ada tugas</p>
                <p class="st-empty-desc">Belum ada tugas yang diterbitkan untuk kelas Anda.</p>
            </div>
        @endforelse

        <div class="st-pagination">{{ $assignments->appends(request()->query())->links() }}</div>
    @endunless
</div>
@endsection
