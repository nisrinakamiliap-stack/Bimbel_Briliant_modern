@extends('layouts.student')

@section('title', 'Materi Pembelajaran — Portal Murid')

@section('content')
<div class="st-container st-page">
    <div class="st-page-header">
        <h1 class="st-page-title">Materi Pembelajaran</h1>
        <p class="st-page-sub">Bahan ajar yang diterbitkan untuk kelas Anda.</p>
    </div>

    @unless ($hasClasses)
        <div class="st-empty">
            <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
            <p class="st-empty-title">Belum ada kelas aktif</p>
            <p class="st-empty-desc">Materi akan tampil setelah Anda terdaftar di kelas.</p>
        </div>
    @else
        @if ($myClassrooms->count() > 1)
        <form method="GET" action="{{ route('student.materials.index') }}" style="margin-bottom:1.5rem;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
            <label for="kelas" class="st-label" style="margin:0;white-space:nowrap;">Filter Kelas:</label>
            <select id="kelas" name="kelas" class="st-select" style="max-width:18rem;" onchange="this.form.submit()">
                <option value="0">Semua Kelas</option>
                @foreach ($myClassrooms as $cls)
                    <option value="{{ $cls->id }}" @selected($classroomFilter === $cls->id)>{{ $cls->name }}</option>
                @endforeach
            </select>
        </form>
        @endif

        @forelse ($materials as $material)
            <a href="{{ route('student.learning.materials.show', $material) }}" class="st-card-link">
                <div style="display:flex;align-items:flex-start;gap:1rem;">
                    <span style="flex-shrink:0;width:2.5rem;height:2.5rem;background:var(--st-mint-bg);border-radius:.625rem;display:flex;align-items:center;justify-content:center;color:var(--st-primary);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </span>
                    <div>
                        <p class="st-card-title">{{ $material->title }}</p>
                        <p class="st-card-meta">
                            {{ $material->classroom->name }}
                            @if ($material->published_at) · {{ $material->published_at->translatedFormat('d M Y') }} @endif
                        </p>
                        @if ($material->description)
                            <p style="margin:.25rem 0 0;font-size:.875rem;color:var(--st-muted);">{{ \Illuminate\Support\Str::limit($material->description, 120) }}</p>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="st-empty">
                <div class="st-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                <p class="st-empty-title">Belum ada materi</p>
                <p class="st-empty-desc">Materi akan tampil setelah guru menerbitkannya.</p>
            </div>
        @endforelse

        <div class="st-pagination">{{ $materials->appends(request()->query())->links() }}</div>
    @endunless
</div>
@endsection
