@extends('layouts.teacher')

@section('title', 'Absensi Murid')

@php
$hasFilters = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();
@endphp

@section('content')
    <x-admin.flash />

    <div class="teacher-page-head">
        <div>
            <h1>Absensi Murid</h1>
            <p>Riwayat kehadiran murid di kelas Anda. Ringkasan hanya menghitung sesi yang sudah dicatat, jadi jadwal yang belum berlangsung tidak dihitung sebagai alpa.</p>
        </div>
        <a href="{{ route('teacher.attendances.create') }}" class="admin-btn admin-btn-primary" id="btn-record-attendance">Catat Absensi</a>
    </div>

    <div class="teacher-status-row" aria-label="Ringkasan status kehadiran">
        @foreach ($statuses as $status)
            <div class="teacher-status-chip"><strong>{{ $statusCounts[$status->value] }}</strong> {{ $status->label() }}</div>
        @endforeach
    </div>

    <section class="admin-panel" aria-labelledby="attendance-history-heading">
        <h2 id="attendance-history-heading" class="sr-only">Riwayat absensi</h2>
        <form action="{{ route('teacher.attendances.index') }}" method="GET" class="admin-toolbar is-stacked">
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
            </div>
            <div class="admin-toolbar-row">
                <label for="dari" class="sr-only">Dari tanggal</label>
                <input type="date" id="dari" name="dari" value="{{ $filters['dari'] ?? '' }}" class="admin-input" style="flex: 1; min-width: 10rem;" title="Dari tanggal">
                <label for="sampai" class="sr-only">Sampai tanggal</label>
                <input type="date" id="sampai" name="sampai" value="{{ $filters['sampai'] ?? '' }}" class="admin-input" style="flex: 1; min-width: 10rem;" title="Sampai tanggal">
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($hasFilters)
                    <a href="{{ route('teacher.attendances.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
            @error('sampai') <p class="admin-error" style="margin: 0;">{{ $message }}</p> @enderror
            @error('kelas') <p class="admin-error" style="margin: 0;">{{ $message }}</p> @enderror
        </form>

        @if ($records->isEmpty())
            @if ($hasFilters)
                <x-admin.empty-state title="Tidak ada catatan yang cocok" icon="search">Ubah filter atau reset untuk melihat seluruh riwayat.</x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada absensi tercatat" icon="attendance">Klik Catat Absensi setelah sesi kelas dimulai untuk mencatat kehadiran murid.</x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Murid</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Status</th>
                            <th scope="col">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td>
                                    {{ $record->session_date->translatedFormat('d M Y') }}
                                    <span class="admin-cell-sub">{{ $record->schedule->timeRange() }}</span>
                                </td>
                                <td class="admin-cell-name">{{ $record->student->name }}</td>
                                <td>{{ $record->schedule->classroom->name }}</td>
                                <td><span class="admin-badge {{ $record->status->badgeClass() }}">{{ $record->status->label() }}</span></td>
                                <td>{{ $record->notes ?? 'Tanpa keterangan' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($records->hasPages())
                {{ $records->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $records->total() }} catatan.</p>
            @endif
        @endif
    </section>
@endsection
