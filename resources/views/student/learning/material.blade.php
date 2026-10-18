@extends('layouts.app')

@section('title', $material->title.' — Bimbel Sentra Edukasi')

@push('styles')
    @include('auth.partials.styles')
@endpush

@section('content')
<section class="auth-section">
    <div class="container-main" style="max-width: 860px;">
        <p style="margin: 0 0 1rem;"><a href="{{ route('student.learning.index') }}" class="auth-link">← Kembali ke Pembelajaran Saya</a></p>
        <div class="auth-card">
            <h1>{{ $material->title }}</h1>
            <p class="auth-subtitle">{{ $material->classroom->name }} · Terbit {{ $material->published_at?->translatedFormat('d F Y') }}</p>
            @if ($material->description)<p>{{ $material->description }}</p>@endif
            @if ($material->content)<div style="white-space: pre-line; line-height: 1.7; color: #253247;">{{ $material->content }}</div>@endif
            @if ($material->link_url)
                <p style="margin-top: 1rem;"><a href="{{ $material->link_url }}" target="_blank" rel="noopener noreferrer" class="auth-link">Buka tautan materi</a></p>
            @endif
            @if ($material->hasFile())
                <p style="margin-top: 1rem;"><a href="{{ route('learning.files.material', $material) }}" class="auth-btn auth-btn--auto" style="padding: .625rem 1.25rem; font-size: .9375rem;">Unduh {{ $material->file_original_name }}</a></p>
            @endif
        </div>
    </div>
</section>
@endsection
