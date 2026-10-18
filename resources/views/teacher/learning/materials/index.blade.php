@extends('layouts.teacher')

@section('title', 'Materi Pembelajaran')

@php
$hasFilters = filled($filters['q'] ?? null) || filled($filters['kelas'] ?? null) || filled($filters['status'] ?? null);
@endphp

@section('content')
    <x-admin.flash />

    <div class="teacher-page-head">
        <div>
            <h1>Materi Pembelajaran</h1>
            <p>Materi untuk kelas yang Anda ampu. Hanya materi berstatus Terbit yang dapat dibuka murid peserta kelas.</p>
        </div>
        @if ($classrooms->isNotEmpty())
            <a href="{{ route('teacher.learning.materials.create') }}" class="admin-btn admin-btn-primary" id="btn-add-material">
                <x-admin.icon name="plus" :size="16" /> Tambah Materi
            </a>
        @endif
    </div>

    @include('teacher.learning.partials.tabs')

    <section class="admin-panel" aria-labelledby="materials-heading">
        <h2 id="materials-heading" class="sr-only">Daftar materi</h2>
        <form action="{{ route('teacher.learning.materials.index') }}" method="GET" class="admin-toolbar" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari judul materi</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="admin-input" placeholder="Cari judul materi" maxlength="100">
            </div>
            <div class="admin-toolbar-row">
                <label for="kelas" class="sr-only">Filter kelas</label>
                <select id="kelas" name="kelas" class="admin-select">
                    <option value="">Semua kelas</option>
                    @foreach ($classrooms as $classroomOption)
                        <option value="{{ $classroomOption->id }}" @selected((string) ($filters['kelas'] ?? '') === (string) $classroomOption->id)>{{ $classroomOption->name }}</option>
                    @endforeach
                </select>
                <label for="status" class="sr-only">Filter status</label>
                <select id="status" name="status" class="admin-select">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected(($filters['status'] ?? null) === $statusOption->value)>{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($hasFilters)
                    <a href="{{ route('teacher.learning.materials.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($materials->isEmpty())
            @if ($hasFilters)
                <x-admin.empty-state title="Materi tidak ditemukan" icon="search">Tidak ada materi yang cocok dengan pencarian atau filter. Coba kata kunci lain atau reset filter.</x-admin.empty-state>
            @elseif ($classrooms->isEmpty())
                <x-admin.empty-state title="Anda belum mengampu kelas" icon="classes">Materi dapat ditambahkan setelah admin menugaskan Anda ke sebuah kelas.</x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada materi" icon="learning">Klik Tambah Materi untuk mengunggah bahan ajar pertama untuk kelas Anda.</x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Judul Materi</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Terbit</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materials as $material)
                            <tr>
                                <td>
                                    <span class="admin-cell-name">{{ $material->title }}</span>
                                    <span class="admin-cell-sub">{{ $material->hasFile() ? 'Berkas: '.$material->file_original_name : ($material->link_url ? 'Tautan' : 'Teks') }} · {{ $material->assignments_count }} tugas terkait</span>
                                </td>
                                <td>{{ $material->classroom->name }}</td>
                                <td>{{ $material->published_at?->translatedFormat('d M Y') ?? 'Belum terbit' }}</td>
                                <td><span class="admin-badge {{ $material->status->badgeClass() }}">{{ $material->status->label() }}</span></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('teacher.learning.materials.show', $material) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Detail {{ $material->title }}">Detail</a>
                                        <a href="{{ route('teacher.learning.materials.edit', $material) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Edit {{ $material->title }}">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($materials->hasPages())
                {{ $materials->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $materials->total() }} materi.</p>
            @endif
        @endif
    </section>
@endsection
