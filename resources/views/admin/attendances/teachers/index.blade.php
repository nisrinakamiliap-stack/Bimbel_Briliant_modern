@extends('layouts.admin')

@section('title', 'Absensi Guru')
@section('page_title', 'Absensi')

@php
$search = $filters['q'] ?? '';
$hasFilters = $search !== '' || ! empty($filters['kelas']) || ! empty($filters['status']) || ! empty($filters['dari']) || ! empty($filters['sampai']);
@endphp

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Absensi Guru</h2>
            <p>Riwayat check-in dan check-out guru per sesi mengajar. Waktu dicatat oleh server ({{ config('app.timezone') }}).</p>
        </div>
    </div>

    @include('admin.attendances.partials.tabs')
    @include('admin.attendances.partials.status-summary', ['statuses' => $statuses, 'statusCounts' => $statusCounts])

    <section class="admin-panel" aria-labelledby="teacher-attendance-heading">
        <h3 id="teacher-attendance-heading" class="sr-only">Daftar absensi guru</h3>

        <form action="{{ route('admin.attendances.teachers.index') }}" method="GET" class="admin-toolbar is-stacked" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama guru</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $search }}" class="admin-input" placeholder="Cari nama guru..." maxlength="100">
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
                <label for="dari" class="sr-only">Periode dari</label>
                <input type="date" id="dari" name="dari" value="{{ $filters['dari'] ?? '' }}" class="admin-input" style="flex: 1; min-width: 10rem;" title="Periode dari">
                <label for="sampai" class="sr-only">Periode sampai</label>
                <input type="date" id="sampai" name="sampai" value="{{ $filters['sampai'] ?? '' }}" class="admin-input" style="flex: 1; min-width: 10rem;" title="Periode sampai">
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.attendances.teachers.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
            @error('sampai') <p class="admin-error" style="margin: 0;">{{ $message }}</p> @enderror
        </form>

        @if ($records->isEmpty())
            @if ($hasFilters)
                <x-admin.empty-state title="Absensi guru tidak ditemukan" icon="search">
                    Tidak ada catatan yang cocok dengan pencarian atau filter Anda.
                </x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada check-in guru" icon="attendance">
                    Catatan muncul setelah guru melakukan check-in pada sesi mengajarnya melalui Dashboard Guru.
                </x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Guru</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Jam Check-in</th>
                            <th scope="col">Jam Check-out</th>
                            <th scope="col">Durasi</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td class="is-muted">{{ $records->firstItem() + $loop->index }}</td>
                                <td class="admin-cell-name">{{ $record->teacher->name }}</td>
                                <td>
                                    {{ $record->schedule->classroom->name }}
                                    <span class="admin-cell-sub">Jadwal {{ $record->schedule->summary() }}</span>
                                </td>
                                <td>{{ $record->session_date->translatedFormat('d M Y') }}</td>
                                <td>{{ $record->checked_in_at->format('H:i') }}</td>
                                <td>{{ $record->checked_out_at?->format('H:i') ?? 'Belum check-out' }}</td>
                                <td>{{ $record->durationLabel() }}</td>
                                <td>
                                    <span class="admin-badge {{ $record->status->badgeClass() }}">{{ $record->status->label() }}</span>
                                    @if ($record->wasCorrected())
                                        <span class="admin-cell-sub">Dikoreksi dari {{ $record->previous_status?->label() }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.attendances.teachers.edit', $record) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Koreksi absensi {{ $record->teacher->name }} {{ $record->session_date->format('d-m-Y') }}">
                                        <x-admin.icon name="edit" :size="14" /> Koreksi
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($records->hasPages())
                {{ $records->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $records->total() }} catatan absensi guru.</p>
            @endif
        @endif
    </section>
@endsection
