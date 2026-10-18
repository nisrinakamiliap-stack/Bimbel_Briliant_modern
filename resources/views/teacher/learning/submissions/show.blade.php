@extends('layouts.teacher')

@section('title', 'Pengumpulan '.$submission->student->name)

@php
$assignment = $submission->assignment;
$grade = $submission->grade;
@endphp

@section('content')
    <nav aria-label="Breadcrumb">
        <ol class="admin-breadcrumb">
            <li><a href="{{ route('teacher.learning.submissions.index') }}">Pengumpulan Tugas</a></li>
            <li><a href="{{ route('teacher.learning.assignments.show', $assignment) }}">{{ $assignment->title }}</a></li>
            <li aria-current="page">{{ $submission->student->name }}</li>
        </ol>
    </nav>

    <div class="teacher-page-head">
        <div>
            <h1>{{ $submission->student->name }}</h1>
            <p>{{ $assignment->title }} · {{ $assignment->classroom->name }}</p>
        </div>
        <a href="{{ route('teacher.learning.assignments.show', $assignment) }}" class="admin-btn admin-btn-secondary">Kembali ke Tugas</a>
    </div>

    <x-admin.flash />

    <div class="teacher-columns">
        <section class="admin-panel" aria-labelledby="answer-heading">
            <div class="admin-panel-header">
                <div><h2 id="answer-heading">Jawaban Murid</h2></div>
                <span class="admin-badge {{ $submission->statusBadgeClass() }}">{{ $submission->statusLabel() }}</span>
            </div>
            <dl class="admin-detail-list">
                <div><dt>Waktu kirim (server)</dt><dd>{{ $submission->submitted_at->translatedFormat('l, d F Y, H:i') }}</dd></div>
                <div><dt>Ketepatan waktu</dt><dd>{{ $submission->is_late ? 'Terlambat' : 'Tepat waktu' }} (tenggat {{ $assignment->due_at->translatedFormat('d F Y, H:i') }})</dd></div>
                <div><dt>Diperbarui murid</dt><dd>{{ $submission->revision_count }} kali</dd></div>
                <div><dt>Berkas jawaban</dt><dd>@if ($submission->hasFile())<a href="{{ route('learning.files.submission', $submission) }}" class="teacher-link" id="link-submission-file" style="overflow-wrap: anywhere;">{{ $submission->file_original_name }}</a>@else Tidak ada berkas @endif</dd></div>
                <div style="grid-column: 1 / -1;"><dt>Jawaban teks</dt><dd style="white-space: pre-line;">{{ $submission->answer_text ?? 'Murid tidak menulis jawaban teks.' }}</dd></div>
            </dl>
        </section>

        <section class="admin-panel" aria-labelledby="grade-heading" id="panel-grade">
            <div class="admin-panel-header">
                <div>
                    <h2 id="grade-heading">{{ $grade ? 'Ubah Penilaian' : 'Beri Penilaian' }}</h2>
                    <p>
                        Skala 0 sampai {{ $assignment->max_score }}.
                        @if ($grade)
                            Dinilai {{ $grade->formattedScore() }} oleh {{ $grade->grader?->name ?? 'pengguna yang sudah dihapus' }} pada {{ $grade->graded_at->translatedFormat('d M Y, H:i') }}{{ $grade->revision_count > 0 ? ' (diubah '.$grade->revision_count.' kali, nilai sebelumnya '.rtrim(rtrim((string) $grade->previous_score, '0'), '.').')' : '' }}.
                        @endif
                    </p>
                </div>
            </div>
            <form action="{{ route('teacher.learning.grades.store', $submission) }}" method="POST" class="admin-form"
                  @if ($grade) data-confirm="Ubah nilai pengumpulan ini? Nilai sebelumnya tetap dicatat." data-confirm-title="Ubah penilaian" data-confirm-button="Ya, ubah nilai" @endif>
                @csrf
                <div class="admin-form-grid">
                    <div class="admin-field is-full">
                        <label for="score" class="admin-label">Nilai <span class="is-required" aria-hidden="true">*</span></label>
                        <input type="text" id="score" name="score" inputmode="decimal" value="{{ old('score', $grade?->formattedScore()) }}" class="admin-input {{ $errors->has('score') ? 'is-invalid' : '' }}" required aria-describedby="score-hint">
                        <p class="admin-hint" id="score-hint">Gunakan koma atau titik untuk desimal, misalnya 87,5.</p>
                        @error('score') <p class="admin-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="admin-field is-full">
                        <label for="feedback" class="admin-label">Umpan balik <span class="is-optional">(opsional)</span></label>
                        <textarea id="feedback" name="feedback" class="admin-textarea {{ $errors->has('feedback') ? 'is-invalid' : '' }}" rows="5" maxlength="2000">{{ old('feedback', $grade?->feedback) }}</textarea>
                        @error('feedback') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary" id="btn-save-grade" data-loading-label="Menyimpan...">{{ $grade ? 'Simpan Perubahan Nilai' : 'Simpan Nilai' }}</button>
                </div>
            </form>
        </section>
    </div>
@endsection
