@extends('layouts.admin')

@section('title', 'Materi Pembelajaran')
@section('page_title', 'Pembelajaran')

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Materi Pembelajaran</h2>
            <p>Kelola materi untuk setiap kelas. Hanya materi berstatus Terbit yang dapat dilihat murid peserta kelas.</p>
        </div>
        <a href="{{ route('admin.learning.materials.create') }}" class="admin-btn admin-btn-primary" id="btn-add-material">
            <x-admin.icon name="plus" :size="16" /> Tambah Materi
        </a>
    </div>

    @include('admin.learning.partials.tabs')

    <section class="admin-panel" aria-labelledby="materials-heading">
        <h3 id="materials-heading" class="sr-only">Daftar materi</h3>
        <form action="{{ route('admin.learning.materials.index') }}" method="GET" class="admin-toolbar" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari judul materi</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="admin-input" placeholder="Cari judul materi..." maxlength="100">
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
            </div>
        </form>

        @if ($materials->isEmpty())
            <x-admin.empty-state title="Belum ada materi" icon="programs">
                Tambahkan materi pembelajaran untuk kelas yang sudah tersedia.
            </x-admin.empty-state>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Judul Materi</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Dibuat Oleh</th>
                            <th scope="col">Terbit</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materials as $material)
                            <tr>
                                <td class="is-muted">{{ $materials->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="admin-cell-name">{{ $material->title }}</span>
                                    <span class="admin-cell-sub">{{ $material->hasFile() ? 'Berkas: '.$material->file_original_name : ($material->link_url ? 'Tautan' : 'Teks') }} · {{ $material->assignments_count }} tugas terkait</span>
                                </td>
                                <td>
                                    {{ $material->classroom->name }}
                                    <span class="admin-cell-sub">Guru: {{ $material->classroom->teacher?->name ?? 'belum ditentukan' }}</span>
                                </td>
                                <td>{{ $material->author?->name ?? '—' }}</td>
                                <td>{{ $material->published_at?->translatedFormat('d M Y') ?? '—' }}</td>
                                <td><span class="admin-badge {{ $material->status->badgeClass() }}">{{ $material->status->label() }}</span></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('admin.learning.materials.show', $material) }}" class="admin-btn admin-btn-secondary admin-btn-sm"><x-admin.icon name="eye" :size="14" /> Detail</a>
                                        <a href="{{ route('admin.learning.materials.edit', $material) }}" class="admin-btn admin-btn-secondary admin-btn-sm"><x-admin.icon name="edit" :size="14" /> Edit</a>
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
