@extends('layouts.app')

@section('title', 'Pembelajaran Saya — Bimbel Sentra Edukasi')

@push('styles')
    @include('auth.partials.styles')
@endpush

@section('content')
<section class="auth-section">
    <div class="container-main" style="max-width: 960px;">
        @if (session('success'))
            <div class="auth-alert auth-alert--success" role="status"><span>{{ session('success') }}</span></div>
        @endif

        <div class="auth-card" style="margin-bottom: 1.5rem;">
            <h1>Pembelajaran Saya</h1>
            <p class="auth-subtitle" style="margin-bottom: 0;">Materi dan tugas dari kelas tempat Anda terdaftar.</p>
        </div>

        @unless ($hasClasses)
            <div class="auth-card"><p class="auth-subtitle" style="margin: 0;">Anda belum ditempatkan di kelas mana pun. Materi dan tugas akan tampil setelah pendaftaran Anda diterima dan kelas ditentukan.</p></div>
        @else
            <div class="auth-card" style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.125rem; font-weight: 800; color: #142B4A; margin: 0 0 1rem;">Tugas</h2>
                @forelse ($assignments as $assignment)
                    @php $mySubmission = $assignment->submissions->first(); @endphp
                    <a href="{{ route('student.learning.assignments.show', $assignment) }}" style="display: block; padding: .875rem 1rem; border: 1px solid #e2e8f0; border-radius: .75rem; margin-bottom: .75rem; text-decoration: none; color: inherit;">
                        <strong style="color: #142B4A;">{{ $assignment->title }}</strong>
                        <span style="display: block; font-size: .875rem; color: #64748b;">
                            {{ $assignment->classroom->name }} · Tenggat {{ $assignment->due_at->translatedFormat('d M Y, H:i') }} ·
                            @if ($mySubmission?->grade)
                                Nilai {{ $mySubmission->grade->formattedScore() }}/{{ $assignment->max_score }}
                            @elseif ($mySubmission)
                                {{ $mySubmission->statusLabel() }}
                            @else
                                Belum dikumpulkan
                            @endif
                        </span>
                    </a>
                @empty
                    <p class="auth-subtitle" style="margin: 0;">Belum ada tugas.</p>
                @endforelse
            </div>

            <div class="auth-card">
                <h2 style="font-size: 1.125rem; font-weight: 800; color: #142B4A; margin: 0 0 1rem;">Materi</h2>
                @forelse ($materials as $material)
                    <a href="{{ route('student.learning.materials.show', $material) }}" style="display: block; padding: .875rem 1rem; border: 1px solid #e2e8f0; border-radius: .75rem; margin-bottom: .75rem; text-decoration: none; color: inherit;">
                        <strong style="color: #142B4A;">{{ $material->title }}</strong>
                        <span style="display: block; font-size: .875rem; color: #64748b;">{{ $material->classroom->name }} · Terbit {{ $material->published_at?->translatedFormat('d M Y') }}</span>
                    </a>
                @empty
                    <p class="auth-subtitle" style="margin: 0;">Belum ada materi.</p>
                @endforelse
            </div>
        @endunless
    </div>
</section>
@endsection
