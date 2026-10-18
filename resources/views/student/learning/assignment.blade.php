@extends('layouts.app')

@section('title', $assignment->title.' — Bimbel Sentra Edukasi')

@push('styles')
    @include('auth.partials.styles')
@endpush

@section('content')
<section class="auth-section">
    <div class="container-main" style="max-width: 860px;">
        <p style="margin: 0 0 1rem;"><a href="{{ route('student.learning.index') }}" class="auth-link">← Kembali ke Pembelajaran Saya</a></p>

        @if (session('success'))
            <div class="auth-alert auth-alert--success" role="status"><span>{{ session('success') }}</span></div>
        @endif

        <div class="auth-card" style="margin-bottom: 1.5rem;">
            <h1>{{ $assignment->title }}</h1>
            <p class="auth-subtitle">{{ $assignment->classroom->name }} · Tenggat <strong>{{ $assignment->due_at->translatedFormat('l, d F Y, H:i') }}</strong> · Skala nilai 0–{{ $assignment->max_score }}</p>
            <div style="white-space: pre-line; line-height: 1.7; color: #253247;">{{ $assignment->instructions }}</div>
            @if ($assignment->hasAttachment())
                <p style="margin-top: 1rem;"><a href="{{ route('learning.files.assignment', $assignment) }}" class="auth-link">Unduh lampiran: {{ $assignment->attachment_original_name }}</a></p>
            @endif
            @if ($assignment->material && $assignment->material->status->value === 'published')
                <p style="margin-top: .5rem;"><a href="{{ route('student.learning.materials.show', $assignment->material) }}" class="auth-link">Materi terkait: {{ $assignment->material->title }}</a></p>
            @endif
        </div>

        @if ($submission)
            <div class="auth-card" style="margin-bottom: 1.5rem;" id="my-submission">
                <h2 style="font-size: 1.125rem; font-weight: 800; color: #142B4A; margin: 0 0 .5rem;">Pengumpulan Saya</h2>
                <p class="auth-subtitle" style="margin-bottom: .75rem;">
                    Dikirim {{ $submission->submitted_at->translatedFormat('d F Y, H:i') }} · Status: <strong>{{ $submission->statusLabel() }}</strong>{{ $submission->is_late && $submission->grade ? ' (terlambat)' : '' }}
                </p>
                @if ($submission->answer_text)<div style="white-space: pre-line; padding: .75rem 1rem; background: #f8fafc; border-radius: .5rem;">{{ $submission->answer_text }}</div>@endif
                @if ($submission->hasFile())<p style="margin-top: .75rem;"><a href="{{ route('learning.files.submission', $submission) }}" class="auth-link">{{ $submission->file_original_name }}</a></p>@endif

                @if ($submission->grade)
                    <div style="margin-top: 1rem; padding: 1rem; border: 1px solid #86efac; background: #f0fdf4; border-radius: .75rem;" id="my-grade">
                        <strong style="font-size: 1.25rem; color: #166534;">Nilai: {{ $submission->grade->formattedScore() }} / {{ $assignment->max_score }}</strong>
                        @if ($submission->grade->feedback)<p style="margin: .5rem 0 0; white-space: pre-line;">{{ $submission->grade->feedback }}</p>@endif
                    </div>
                @endif
            </div>
        @endif

        <div class="auth-card">
            @if ($submitDecision->allowed())
                <h2 style="font-size: 1.125rem; font-weight: 800; color: #142B4A; margin: 0 0 1rem;">{{ $submission ? 'Perbarui Pengumpulan' : 'Kumpulkan Tugas' }}</h2>
                @if ($assignment->isPastDue(now()))
                    <div class="auth-alert auth-alert--error" role="alert"><span>Tenggat sudah lewat. Pengumpulan akan tercatat terlambat.</span></div>
                @endif
                <form action="{{ route('student.learning.assignments.submit', $assignment) }}" method="POST" enctype="multipart/form-data" class="auth-form" novalidate>
                    @csrf
                    <div>
                        <label for="answer_text" class="auth-label">Jawaban</label>
                        <textarea id="answer_text" name="answer_text" rows="6" class="auth-input {{ $errors->has('answer_text') ? 'is-invalid' : '' }}">{{ old('answer_text', $submission?->answer_text) }}</textarea>
                        @error('answer_text') <p class="auth-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="file" class="auth-label">Berkas Jawaban <span style="font-weight: 400; color: #64748b;">(opsional, PDF/Word/JPG/PNG/ZIP, maks. 10 MB)</span></label>
                        <input type="file" id="file" name="file" class="auth-input {{ $errors->has('file') ? 'is-invalid' : '' }}" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip">
                        @error('file') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="auth-btn" id="btn-submit-assignment">{{ $submission ? 'Kirim Ulang' : 'Kumpulkan' }}</button>
                </form>
            @else
                <p class="auth-subtitle" style="margin: 0;" id="submit-closed">{{ $submitDecision->message() }}</p>
            @endif
        </div>
    </div>
</section>
@endsection
