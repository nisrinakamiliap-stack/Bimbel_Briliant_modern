@extends('layouts.admin')

@section('title', 'Tugas')
@section('page_title', 'Pembelajaran')

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Tugas</h2>
            <p>Kelola tugas per kelas beserta tenggat, skala nilai, dan aturan pengumpulan.</p>
        </div>
        <a href="{{ route('admin.learning.assignments.create') }}" class="admin-btn admin-btn-primary" id="btn-add-assignment">
            <x-admin.icon name="plus" :size="16" /> Tambah Tugas
        </a>
    </div>

    @include('admin.learning.partials.tabs')

    <section class="admin-panel" aria-labelledby="assignments-heading">
        <h3 id="assignments-heading" class="sr-only">Daftar tugas</h3>
        <form action="{{ route('admin.learning.assignments.index') }}" method="GET" class="admin-toolbar" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari judul tugas</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="admin-input" placeholder="Cari judul tugas..." maxlength="100">
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

        @if ($assignments->isEmpty())
            <x-admin.empty-state title="Belum ada tugas" icon="enrollments">Buat tugas untuk kelas yang sudah tersedia.</x-admin.empty-state>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Judul Tugas</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Tenggat</th>
                            <th scope="col">Pengumpulan</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td class="is-muted">{{ $assignments->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="admin-cell-name">{{ $assignment->title }}</span>
                                    <span class="admin-cell-sub">Skala 0–{{ $assignment->max_score }}{{ $assignment->allow_late_submission ? ' · menerima terlambat' : '' }}</span>
                                </td>
                                <td>{{ $assignment->classroom->name }}<span class="admin-cell-sub">Guru: {{ $assignment->classroom->teacher?->name ?? 'belum ditentukan' }}</span></td>
                                <td>
                                    {{ $assignment->due_at->translatedFormat('d M Y, H:i') }}
                                    @if ($assignment->isPastDue(now()))<span class="admin-cell-sub">Tenggat sudah lewat</span>@endif
                                </td>
                                <td>{{ $assignment->submissions_count }}</td>
                                <td><span class="admin-badge {{ $assignment->status->badgeClass() }}">{{ $assignment->status->label() }}</span></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('admin.learning.assignments.show', $assignment) }}" class="admin-btn admin-btn-secondary admin-btn-sm"><x-admin.icon name="eye" :size="14" /> Detail</a>
                                        <a href="{{ route('admin.learning.assignments.edit', $assignment) }}" class="admin-btn admin-btn-secondary admin-btn-sm"><x-admin.icon name="edit" :size="14" /> Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($assignments->hasPages())
                {{ $assignments->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $assignments->total() }} tugas.</p>
            @endif
        @endif
    </section>
@endsection
