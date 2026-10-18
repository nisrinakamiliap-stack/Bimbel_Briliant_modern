@extends('layouts.admin')

@section('title', $assignment->title.' — Tugas')
@section('page_title', 'Pembelajaran')

@php $submittedCount = $roster->filter(fn ($enrollment) => $submissions->has($enrollment->student_id))->count(); @endphp

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.learning.assignments.index') }}">Tugas</a></li>
                <li aria-current="page">{{ $assignment->title }}</li>
            </ol>
            <h2>Detail Tugas</h2>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.learning.assignments.index') }}" class="admin-btn admin-btn-secondary"><x-admin.icon name="arrow-left" :size="16" /> Kembali</a>
            <a href="{{ route('admin.learning.assignments.edit', $assignment) }}" class="admin-btn admin-btn-primary"><x-admin.icon name="edit" :size="16" /> Edit</a>
        </div>
    </div>

    <x-admin.flash />

    <div class="admin-grid">
        <section class="admin-panel is-wide" aria-labelledby="assignment-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="assignment-heading">{{ $assignment->title }}</h3>
                    <p>{{ $assignment->classroom->name }} · Guru: {{ $assignment->classroom->teacher?->name ?? 'belum ditentukan' }}</p>
                </div>
                <span class="admin-badge {{ $assignment->status->badgeClass() }}">{{ $assignment->status->label() }}</span>
            </div>
            <dl class="admin-detail-list">
                <div><dt>Tenggat</dt><dd>{{ $assignment->due_at->translatedFormat('l, d F Y, H:i') }}{{ $assignment->isPastDue(now()) ? ' (sudah lewat)' : '' }}</dd></div>
                <div><dt>Skala Nilai</dt><dd>0–{{ $assignment->max_score }}</dd></div>
                <div><dt>Pengumpulan Terlambat</dt><dd>{{ $assignment->allow_late_submission ? 'Diterima (ditandai terlambat)' : 'Tidak diterima' }}</dd></div>
                <div><dt>Perbarui Pengumpulan</dt><dd>{{ $assignment->allow_resubmission ? 'Diizinkan sebelum dinilai' : 'Tidak diizinkan' }}</dd></div>
                <div><dt>Materi Terkait</dt><dd>@if ($assignment->material)<a href="{{ route('admin.learning.materials.show', $assignment->material) }}" style="color: var(--admin-primary);">{{ $assignment->material->title }}</a>@else — @endif</dd></div>
                <div><dt>Lampiran</dt><dd>@if ($assignment->hasAttachment())<a href="{{ route('learning.files.assignment', $assignment) }}" style="color: var(--admin-primary);">{{ $assignment->attachment_original_name }}</a>@else — @endif</dd></div>
                <div><dt>Dibuat Oleh</dt><dd>{{ $assignment->author?->name ?? '—' }}</dd></div>
                <div><dt>Diterbitkan</dt><dd>{{ $assignment->published_at?->translatedFormat('d F Y, H:i') ?? 'Belum diterbitkan' }}</dd></div>
                <div style="grid-column: 1 / -1;"><dt>Instruksi</dt><dd style="white-space: pre-line;">{{ $assignment->instructions }}</dd></div>
            </dl>
        </section>

        <section class="admin-panel is-wide" aria-labelledby="roster-heading" id="panel-roster">
            <div class="admin-panel-header">
                <div>
                    <h3 id="roster-heading">Status Pengumpulan Murid</h3>
                    <p>{{ $submittedCount }} dari {{ $roster->count() }} murid peserta sudah mengumpulkan.</p>
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
                                    <td>{{ $rosterSubmission?->submitted_at->translatedFormat('d M Y, H:i') ?? '—' }}</td>
                                    <td>{{ $rosterSubmission?->grade?->formattedScore() ?? '—' }}</td>
                                    <td>
                                        @if ($rosterSubmission)
                                            <a href="{{ route('admin.learning.submissions.show', $rosterSubmission) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Periksa</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-panel is-wide" aria-labelledby="delete-assignment-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="delete-assignment-heading">Hapus Tugas</h3>
                    <p>{{ $canDelete->allowed() ? 'Tugas belum memiliki pengumpulan sehingga aman dihapus.' : $canDelete->message() }}</p>
                </div>
                @if ($canDelete->allowed())
                    <form action="{{ route('admin.learning.assignments.destroy', $assignment) }}" method="POST" data-confirm="Hapus tugas {{ $assignment->title }}? Tindakan ini tidak dapat dibatalkan.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-danger-outline" id="btn-delete-assignment"><x-admin.icon name="trash" :size="16" /> Hapus</button>
                    </form>
                @endif
            </div>
        </section>
    </div>
@endsection
