@extends('layouts.teacher')

@section('title', $isGradingView ? 'Penilaian Tugas' : 'Pengumpulan Tugas')

@php
$listRoute = $isGradingView ? 'teacher.learning.grades.index' : 'teacher.learning.submissions.index';
$statusOptions = [
    'belum_dinilai' => 'Belum dinilai',
    'dinilai' => 'Sudah dinilai',
    'tepat_waktu' => 'Tepat waktu',
    'terlambat' => 'Terlambat',
];
$hasFilters = filled($filters['q'] ?? null) || filled($filters['kelas'] ?? null) || filled($filters['tugas'] ?? null)
    || (filled($filters['status'] ?? null) && ! ($isGradingView && $filters['status'] === 'belum_dinilai'));
@endphp

@section('content')
    <x-admin.flash />

    <div class="teacher-page-head">
        <div>
            <h1>{{ $isGradingView ? 'Penilaian Tugas' : 'Pengumpulan Tugas' }}</h1>
            <p>
                @if ($isGradingView)
                    Pengumpulan yang menunggu nilai Anda. Buka satu pengumpulan untuk memberi nilai dan umpan balik.
                @else
                    Semua jawaban yang dikirim murid untuk tugas di kelas Anda. Waktu kirim dicatat oleh server.
                @endif
            </p>
        </div>
    </div>

    @include('teacher.learning.partials.tabs')

    <section class="admin-panel" aria-labelledby="submissions-heading">
        <h2 id="submissions-heading" class="sr-only">Daftar pengumpulan</h2>
        <form action="{{ route($listRoute) }}" method="GET" class="admin-toolbar is-stacked" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama murid</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="admin-input" placeholder="Cari nama murid" maxlength="100">
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
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($hasFilters)
                    <a href="{{ route($listRoute) }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($submissions->isEmpty())
            @if ($hasFilters)
                <x-admin.empty-state title="Pengumpulan tidak ditemukan" icon="search">Tidak ada pengumpulan yang cocok dengan filter. Coba ubah atau reset filter.</x-admin.empty-state>
            @elseif ($isGradingView)
                <x-admin.empty-state title="Tidak ada yang menunggu nilai" icon="check">Semua pengumpulan sudah dinilai. Pengumpulan baru akan muncul di sini.</x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada pengumpulan" icon="inbox">Jawaban murid akan tampil di sini setelah tugas diterbitkan dan dikumpulkan.</x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Murid</th>
                            <th scope="col">Tugas</th>
                            <th scope="col">Waktu Kirim</th>
                            <th scope="col">Status</th>
                            <th scope="col">Nilai</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $submission)
                            <tr>
                                <td class="admin-cell-name">{{ $submission->student->name }}</td>
                                <td>
                                    {{ $submission->assignment->title }}
                                    <span class="admin-cell-sub">{{ $submission->assignment->classroom->name }}</span>
                                </td>
                                <td>{{ $submission->submitted_at->translatedFormat('d M Y, H:i') }}</td>
                                <td><span class="admin-badge {{ $submission->statusBadgeClass() }}">{{ $submission->statusLabel() }}</span></td>
                                <td>{{ $submission->grade?->formattedScore() ?? 'Belum dinilai' }}</td>
                                <td>
                                    <a href="{{ route('teacher.learning.submissions.show', $submission) }}" class="admin-btn {{ $submission->grade ? 'admin-btn-secondary' : 'admin-btn-primary' }} admin-btn-sm" aria-label="{{ $submission->grade ? 'Lihat' : 'Nilai' }} pengumpulan {{ $submission->student->name }}">{{ $submission->grade ? 'Lihat' : 'Beri Nilai' }}</a>
                                </td>
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
