@extends('layouts.admin')

@section('title', $material->title.' — Materi')
@section('page_title', 'Pembelajaran')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.learning.materials.index') }}">Materi Pembelajaran</a></li>
                <li aria-current="page">{{ $material->title }}</li>
            </ol>
            <h2>Detail Materi</h2>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.learning.materials.index') }}" class="admin-btn admin-btn-secondary"><x-admin.icon name="arrow-left" :size="16" /> Kembali</a>
            <a href="{{ route('admin.learning.assignments.create', ['kelas' => $material->classroom_id]) }}" class="admin-btn admin-btn-secondary"><x-admin.icon name="plus" :size="16" /> Buat Tugas</a>
            <a href="{{ route('admin.learning.materials.edit', $material) }}" class="admin-btn admin-btn-primary"><x-admin.icon name="edit" :size="16" /> Edit</a>
        </div>
    </div>

    <x-admin.flash />

    <div class="admin-grid">
        <section class="admin-panel is-wide" aria-labelledby="material-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="material-heading">{{ $material->title }}</h3>
                    <p>{{ $material->description ?? 'Tanpa deskripsi' }}</p>
                </div>
                <span class="admin-badge {{ $material->status->badgeClass() }}">{{ $material->status->label() }}</span>
            </div>
            <dl class="admin-detail-list">
                <div><dt>Kelas</dt><dd><a href="{{ route('admin.classes.show', $material->classroom) }}" style="color: var(--admin-primary); text-decoration: none; font-weight: 600;">{{ $material->classroom->name }}</a></dd></div>
                <div><dt>Guru Pengajar</dt><dd>{{ $material->classroom->teacher?->name ?? 'Belum ditentukan' }}</dd></div>
                <div><dt>Dibuat Oleh</dt><dd>{{ $material->author?->name ?? '—' }}</dd></div>
                <div><dt>Tanggal Terbit</dt><dd>{{ $material->published_at?->translatedFormat('d F Y, H:i') ?? 'Belum diterbitkan' }}</dd></div>
                <div>
                    <dt>Tautan</dt>
                    <dd>@if ($material->link_url)<a href="{{ $material->link_url }}" target="_blank" rel="noopener noreferrer" style="color: var(--admin-primary);">{{ $material->link_url }}</a>@else — @endif</dd>
                </div>
                <div>
                    <dt>Berkas</dt>
                    <dd>@if ($material->hasFile())<a href="{{ route('learning.files.material', $material) }}" style="color: var(--admin-primary);" id="link-material-file">{{ $material->file_original_name }}</a>@else — @endif</dd>
                </div>
                <div style="grid-column: 1 / -1;"><dt>Isi Materi</dt><dd style="white-space: pre-line;">{{ $material->content ?? '—' }}</dd></div>
            </dl>
        </section>

        <section class="admin-panel is-wide" aria-labelledby="material-assignments-heading">
            <div class="admin-panel-header"><div><h3 id="material-assignments-heading">Tugas Terkait</h3></div></div>
            @if ($material->assignments->isEmpty())
                <x-admin.empty-state title="Belum ada tugas terkait" icon="enrollments">Tugas yang dikaitkan dengan materi ini akan tampil di sini.</x-admin.empty-state>
            @else
                <ul style="margin: 0; padding: 1rem 1.25rem 1.25rem 2.5rem;">
                    @foreach ($material->assignments as $relatedAssignment)
                        <li><a href="{{ route('admin.learning.assignments.show', $relatedAssignment) }}" style="color: var(--admin-primary);">{{ $relatedAssignment->title }}</a> — tenggat {{ $relatedAssignment->due_at->translatedFormat('d M Y, H:i') }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
