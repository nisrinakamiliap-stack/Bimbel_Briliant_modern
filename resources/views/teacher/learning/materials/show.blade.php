@extends('layouts.teacher')

@section('title', $material->title)

@section('content')
    <nav aria-label="Breadcrumb">
        <ol class="admin-breadcrumb">
            <li><a href="{{ route('teacher.learning.materials.index') }}">Materi Pembelajaran</a></li>
            <li aria-current="page">{{ $material->title }}</li>
        </ol>
    </nav>

    <div class="teacher-page-head">
        <div>
            <h1>{{ $material->title }}</h1>
            <p>{{ $material->description ?? 'Tanpa deskripsi.' }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('teacher.learning.assignments.create', ['kelas' => $material->classroom_id]) }}" class="admin-btn admin-btn-secondary">Buat Tugas dari Kelas Ini</a>
            <a href="{{ route('teacher.learning.materials.edit', $material) }}" class="admin-btn admin-btn-primary">Edit Materi</a>
        </div>
    </div>

    <x-admin.flash />

    <div class="teacher-columns">
        <section class="admin-panel" aria-labelledby="material-content-heading">
            <div class="admin-panel-header">
                <div><h2 id="material-content-heading">Isi Materi</h2></div>
                <span class="admin-badge {{ $material->status->badgeClass() }}">{{ $material->status->label() }}</span>
            </div>
            <div style="padding: 0 1.25rem 1.25rem; white-space: pre-line; line-height: 1.7;">{{ $material->content ?? 'Materi ini tidak memiliki isi teks. Lihat tautan atau berkas di samping.' }}</div>
        </section>

        <div class="teacher-stack">
            <section class="admin-panel" aria-labelledby="material-info-heading">
                <div class="admin-panel-header"><div><h2 id="material-info-heading">Informasi</h2></div></div>
                <dl class="admin-detail-list">
                    <div><dt>Kelas</dt><dd><a href="{{ route('teacher.classes.show', $material->classroom) }}" class="teacher-link">{{ $material->classroom->name }}</a></dd></div>
                    <div><dt>Dibuat oleh</dt><dd>{{ $material->author?->name ?? 'Tidak tercatat' }}</dd></div>
                    <div><dt>Tanggal terbit</dt><dd>{{ $material->published_at?->translatedFormat('d F Y, H:i') ?? 'Belum diterbitkan' }}</dd></div>
                    <div>
                        <dt>Tautan</dt>
                        <dd>@if ($material->link_url)<a href="{{ $material->link_url }}" target="_blank" rel="noopener noreferrer" class="teacher-link" style="overflow-wrap: anywhere;">{{ $material->link_url }}</a>@else Tidak ada @endif</dd>
                    </div>
                    <div>
                        <dt>Berkas</dt>
                        <dd>@if ($material->hasFile())<a href="{{ route('learning.files.material', $material) }}" class="teacher-link" id="link-material-file" style="overflow-wrap: anywhere;">{{ $material->file_original_name }}</a>@else Tidak ada @endif</dd>
                    </div>
                </dl>
            </section>

            <section class="admin-panel" aria-labelledby="material-assignments-heading">
                <div class="admin-panel-header"><div><h2 id="material-assignments-heading">Tugas Terkait</h2></div></div>
                @if ($material->assignments->isEmpty())
                    <x-admin.empty-state title="Belum ada tugas terkait" icon="edit">Pilih materi ini saat membuat tugas agar muncul di sini.</x-admin.empty-state>
                @else
                    <ul class="teacher-list">
                        @foreach ($material->assignments as $relatedAssignment)
                            <li class="teacher-list-item">
                                <div>
                                    <a href="{{ route('teacher.learning.assignments.show', $relatedAssignment) }}">{{ $relatedAssignment->title }}</a>
                                    <p>Tenggat {{ $relatedAssignment->due_at->translatedFormat('d M Y, H:i') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endsection
