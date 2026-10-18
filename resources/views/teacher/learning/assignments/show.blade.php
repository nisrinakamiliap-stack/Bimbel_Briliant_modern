@extends('layouts.teacher')

@section('title', $assignment->title)

@php
$submittedCount = $roster->filter(fn ($enrollment) => $submissions->has($enrollment->student_id))->count();
$missingCount = $roster->count() - $submittedCount;
$isPastDue = $assignment->isPastDue(now());
@endphp

@section('content')
    <nav aria-label="Breadcrumb">
        <ol class="admin-breadcrumb">
            <li><a href="{{ route('teacher.learning.assignments.index') }}">Tugas</a></li>
            <li aria-current="page">{{ $assignment->title }}</li>
        </ol>
    </nav>

    <div class="teacher-page-head">
        <div>
            <h1>{{ $assignment->title }}</h1>
            <p>{{ $assignment->classroom->name }} · Tenggat {{ $assignment->due_at->translatedFormat('l, d F Y, H:i') }}{{ $isPastDue ? ' (sudah lewat)' : '' }}</p>
        </div>
        <a href="{{ route('teacher.learning.assignments.edit', $assignment) }}" class="admin-btn admin-btn-primary">Edit Tugas</a>
    </div>

    <x-admin.flash />

    <div class="teacher-columns">
        <section class="admin-panel" aria-labelledby="roster-heading" id="panel-roster">
            <div class="admin-panel-header">
                <div>
                    <h2 id="roster-heading">Status Pengumpulan Murid</h2>
                    <p>
                        {{ $submittedCount }} dari {{ $roster->count() }} murid sudah mengumpulkan.
                        @if ($missingCount > 0)
                            {{ $isPastDue ? $missingCount.' murid tidak mengumpulkan sampai tenggat.' : $missingCount.' murid belum mengumpulkan.' }}
                        @endif
                    </p>
                </div>
            </div>
            @if ($roster->isEmpty())
                <x-admin.empty-state title="Belum ada murid di kelas ini" icon="students">Murid dengan pendaftaran Diterima di kelas ini akan tampil di sini.</x-admin.empty-state>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th scope="col">Murid</th><th scope="col">Status</th><th scope="col">Waktu Kirim</th><th scope="col">Nilai</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
                        <tbody>
                            @foreach ($roster as $enrollment)
                                @php $rosterSubmission = $submissions->get($enrollment->student_id); @endphp
                                <tr>
                                    <td class="admin-cell-name">{{ $enrollment->student->name }}</td>
                                    <td>
                                        @if ($rosterSubmission)
                                            <span class="admin-badge {{ $rosterSubmission->statusBadgeClass() }}">{{ $rosterSubmission->statusLabel() }}</span>
                                        @else
                                            <span class="admin-badge is-neutral">Belum Mengumpulkan</span>
                                        @endif
                                    </td>
                                    <td>{{ $rosterSubmission?->submitted_at->translatedFormat('d M Y, H:i') ?? 'Belum ada' }}</td>
                                    <td>{{ $rosterSubmission?->grade?->formattedScore() ?? 'Belum dinilai' }}</td>
                                    <td>
                                        @if ($rosterSubmission)
                                            <a href="{{ route('teacher.learning.submissions.show', $rosterSubmission) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Periksa pengumpulan {{ $enrollment->student->name }}">Periksa</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="teacher-stack">
            <section class="admin-panel" aria-labelledby="assignment-info-heading">
                <div class="admin-panel-header">
                    <div><h2 id="assignment-info-heading">Detail Tugas</h2></div>
                    <span class="admin-badge {{ $assignment->status->badgeClass() }}">{{ $assignment->status->label() }}</span>
                </div>
                <dl class="admin-detail-list">
                    <div><dt>Skala nilai</dt><dd>0 sampai {{ $assignment->max_score }}</dd></div>
                    <div><dt>Pengumpulan terlambat</dt><dd>{{ $assignment->allow_late_submission ? 'Diterima, ditandai terlambat' : 'Tidak diterima' }}</dd></div>
                    <div><dt>Perbarui pengumpulan</dt><dd>{{ $assignment->allow_resubmission ? 'Diizinkan sebelum dinilai' : 'Tidak diizinkan' }}</dd></div>
                    <div><dt>Materi terkait</dt><dd>@if ($assignment->material)<a href="{{ route('teacher.learning.materials.show', $assignment->material) }}" class="teacher-link">{{ $assignment->material->title }}</a>@else Tidak ada @endif</dd></div>
                    <div><dt>Lampiran</dt><dd>@if ($assignment->hasAttachment())<a href="{{ route('learning.files.assignment', $assignment) }}" class="teacher-link" style="overflow-wrap: anywhere;">{{ $assignment->attachment_original_name }}</a>@else Tidak ada @endif</dd></div>
                    <div><dt>Diterbitkan</dt><dd>{{ $assignment->published_at?->translatedFormat('d F Y, H:i') ?? 'Belum diterbitkan' }}</dd></div>
                    <div style="grid-column: 1 / -1;"><dt>Instruksi</dt><dd style="white-space: pre-line;">{{ $assignment->instructions }}</dd></div>
                </dl>
            </section>

            <section class="admin-panel" aria-labelledby="delete-assignment-heading">
                <div class="admin-panel-header">
                    <div>
                        <h2 id="delete-assignment-heading">Hapus Tugas</h2>
                        <p>{{ $canDelete->allowed() ? 'Tugas belum memiliki pengumpulan sehingga aman dihapus.' : $canDelete->message() }}</p>
                    </div>
                    @if ($canDelete->allowed())
                        <form action="{{ route('teacher.learning.assignments.destroy', $assignment) }}" method="POST" data-confirm="Hapus tugas {{ $assignment->title }}? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus tugas" data-confirm-button="Ya, Hapus">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn-danger-outline" id="btn-delete-assignment">Hapus</button>
                        </form>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
