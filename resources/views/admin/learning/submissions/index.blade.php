@extends('layouts.admin')

@section('title', $isGradingView ? 'Penilaian Tugas' : 'Pengumpulan Tugas')
@section('page_title', 'Pembelajaran')

@php $listRoute = $isGradingView ? 'admin.learning.grades.index' : 'admin.learning.submissions.index'; @endphp

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>{{ $isGradingView ? 'Penilaian Tugas' : 'Pengumpulan Tugas' }}</h2>
            <p>{{ $isGradingView ? 'Pengumpulan yang perlu dinilai dan riwayat penilaian.' : 'Seluruh jawaban yang dikumpulkan murid beserta status keterlambatan.' }}</p>
        </div>
    </div>

    @include('admin.learning.partials.tabs')

    <section class="admin-panel" aria-labelledby="submissions-heading">
        <h3 id="submissions-heading" class="sr-only">Daftar pengumpulan</h3>
        <form action="{{ route($listRoute) }}" method="GET" class="admin-toolbar is-stacked" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama murid</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="admin-input" placeholder="Cari nama murid..." maxlength="100">
            </div>
            <div class="admin-toolbar-row">
                <label for="kelas" class="sr-only">Filter kelas</label>
                <select id="kelas" name="kelas" class="admin-select">
                    <option value="">Semua kelas</option>
                    @foreach ($classrooms as $classroomOption)
                        <option value="{{ $classroomOption->id }}" @selected((string) ($filters['kelas'] ?? '') === (string) $classroomOption->id)>{{ $classroomOption->name }}</option>
                    @endforeach
                </select>
                <label for="tugas" class="sr-only">Filter tugas</label>
                <select id="tugas" name="tugas" class="admin-select">
                    <option value="">Semua tugas</option>
                    @foreach ($assignments as $assignmentOption)
                        <option value="{{ $assignmentOption->id }}" @selected((string) ($filters['tugas'] ?? '') === (string) $assignmentOption->id)>{{ $assignmentOption->title }}</option>
                    @endforeach
                </select>
                <label for="status" class="sr-only">Filter status</label>
                <select id="status" name="status" class="admin-select">
                    <option value="">Semua status</option>
                    <option value="tepat_waktu" @selected(($filters['status'] ?? null) === 'tepat_waktu')>Tepat waktu</option>
                    <option value="terlambat" @selected(($filters['status'] ?? null) === 'terlambat')>Terlambat</option>
                    <option value="belum_dinilai" @selected(($filters['status'] ?? null) === 'belum_dinilai')>Belum dinilai</option>
                    <option value="dinilai" @selected(($filters['status'] ?? null) === 'dinilai')>Sudah dinilai</option>
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
            </div>
        </form>

        @if ($submissions->isEmpty())
            <x-admin.empty-state title="{{ $isGradingView ? 'Tidak ada pengumpulan untuk dinilai' : 'Belum ada pengumpulan' }}" icon="enrollments">
                Pengumpulan muncul setelah murid mengirim jawaban melalui halaman pembelajaran murid.
            </x-admin.empty-state>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Murid</th>
                            <th scope="col">Tugas</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Waktu Kirim</th>
                            <th scope="col">Status</th>
                            <th scope="col">Nilai</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $submission)
                            <tr>
                                <td class="is-muted">{{ $submissions->firstItem() + $loop->index }}</td>
                                <td class="admin-cell-name">{{ $submission->student->name }}</td>
                                <td>{{ $submission->assignment->title }}</td>
                                <td>{{ $submission->assignment->classroom->name }}</td>
                                <td>{{ $submission->submitted_at->translatedFormat('d M Y, H:i') }}</td>
                                <td><span class="admin-badge {{ $submission->statusBadgeClass() }}">{{ $submission->statusLabel() }}</span>@if ($submission->grade && $submission->is_late)<span class="admin-cell-sub">Terlambat</span>@endif</td>
                                <td>{{ $submission->grade ? $submission->grade->formattedScore().' / '.$submission->assignment->max_score : '—' }}</td>
                                <td><a href="{{ route('admin.learning.submissions.show', $submission) }}" class="admin-btn {{ $submission->grade ? 'admin-btn-secondary' : 'admin-btn-primary' }} admin-btn-sm">{{ $submission->grade ? 'Detail' : 'Nilai' }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($submissions->hasPages())
                {{ $submissions->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $submissions->total() }} pengumpulan.</p>
            @endif
        @endif
    </section>
@endsection
