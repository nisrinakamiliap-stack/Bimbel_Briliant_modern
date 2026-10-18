@extends('layouts.admin')

@section('title', 'Absensi Murid')
@section('page_title', 'Absensi')

@php
$search = $filters['q'] ?? '';
$hasFilters = $search !== '' || ! empty($filters['kelas']) || ! empty($filters['guru']) || ! empty($filters['status']) || ! empty($filters['dari']) || ! empty($filters['sampai']);
@endphp

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Absensi Murid</h2>
            <p>Riwayat dan rekap kehadiran murid per sesi belajar (jadwal + tanggal).</p>
        </div>
        <a href="{{ route('admin.attendances.students.create') }}" class="admin-btn admin-btn-primary" id="btn-record-attendance">
            <x-admin.icon name="plus" :size="16" />
            Catat Absensi
        </a>
    </div>

    @include('admin.attendances.partials.tabs')
    @include('admin.attendances.partials.status-summary', ['statuses' => $statuses, 'statusCounts' => $statusCounts])

    <section class="admin-panel" aria-labelledby="attendance-heading">
        <h3 id="attendance-heading" class="sr-only">Daftar absensi murid</h3>

        <form action="{{ route('admin.attendances.students.index') }}" method="GET" class="admin-toolbar is-stacked" role="search">
            <input type="hidden" name="tampilan" value="{{ $viewMode }}">
            @if ($viewMode === 'rekap')
                <input type="hidden" name="kelompok" value="{{ $groupBy }}">
            @endif
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama murid</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $search }}" class="admin-input" placeholder="Cari nama atau email murid..." maxlength="100">
            </div>
            <div class="admin-toolbar-row">
                <label for="kelas" class="sr-only">Filter kelas</label>
                <select id="kelas" name="kelas" class="admin-select">
                    <option value="">Semua kelas</option>
                    @foreach ($classrooms as $classroomOption)
                        <option value="{{ $classroomOption->id }}" @selected((string) ($filters['kelas'] ?? '') === (string) $classroomOption->id)>{{ $classroomOption->name }}</option>
                    @endforeach
                </select>
                <label for="guru" class="sr-only">Filter guru</label>
                <select id="guru" name="guru" class="admin-select">
                    <option value="">Semua guru</option>
                    @foreach ($teachers as $teacherOption)
                        <option value="{{ $teacherOption->id }}" @selected((string) ($filters['guru'] ?? '') === (string) $teacherOption->id)>{{ $teacherOption->name }}</option>
                    @endforeach
                </select>
                @if ($viewMode === 'riwayat')
                    <label for="status" class="sr-only">Filter status</label>
                    <select id="status" name="status" class="admin-select">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption->value }}" @selected(($filters['status'] ?? null) === $statusOption->value)>{{ $statusOption->label() }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div class="admin-toolbar-row">
                <label for="dari" class="sr-only">Periode dari</label>
                <input type="date" id="dari" name="dari" value="{{ $filters['dari'] ?? '' }}" class="admin-input" style="flex: 1; min-width: 10rem;" title="Periode dari">
                <label for="sampai" class="sr-only">Periode sampai</label>
                <input type="date" id="sampai" name="sampai" value="{{ $filters['sampai'] ?? '' }}" class="admin-input" style="flex: 1; min-width: 10rem;" title="Periode sampai">
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.attendances.students.index', ['tampilan' => $viewMode]) }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
            @error('sampai') <p class="admin-error" style="margin: 0;">{{ $message }}</p> @enderror
        </form>

        <div style="display: flex; flex-wrap: wrap; gap: .75rem; padding: 1rem 1.25rem 0;">
            <nav class="admin-view-switch" aria-label="Pilihan tampilan">
                <a href="{{ request()->fullUrlWithQuery(['tampilan' => 'riwayat', 'page' => null]) }}" class="{{ $viewMode === 'riwayat' ? 'is-active' : '' }}">Riwayat</a>
                <a href="{{ request()->fullUrlWithQuery(['tampilan' => 'rekap', 'page' => null, 'status' => null]) }}" class="{{ $viewMode === 'rekap' ? 'is-active' : '' }}">Rekap</a>
            </nav>
            @if ($viewMode === 'rekap')
                <nav class="admin-view-switch" aria-label="Kelompok rekap">
                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'murid', 'page' => null]) }}" class="{{ $groupBy === 'murid' ? 'is-active' : '' }}">Per Murid</a>
                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'kelas', 'page' => null]) }}" class="{{ $groupBy === 'kelas' ? 'is-active' : '' }}">Per Kelas</a>
                </nav>
            @endif
        </div>

        @if ($viewMode === 'rekap')
            @if ($recap->isEmpty())
                <x-admin.empty-state title="Belum ada data rekap" icon="attendance">
                    Tidak ada catatan absensi pada periode atau filter ini.
                </x-admin.empty-state>
            @else
                <div class="admin-table-wrap" style="margin-top: 1rem;">
                    <table class="admin-table" id="attendance-recap">
                        <thead>
                            <tr>
                                <th scope="col">{{ $groupBy === 'kelas' ? 'Kelas' : 'Murid' }}</th>
                                @foreach ($statuses as $statusOption)
                                    <th scope="col">{{ $statusOption->label() }}</th>
                                @endforeach
                                <th scope="col">Total Sesi</th>
                                <th scope="col">% Hadir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recap as $row)
                                <tr>
                                    <td class="admin-cell-name">{{ $row->group_name }}</td>
                                    @foreach ($statuses as $statusOption)
                                        <td>{{ (int) $row->{$statusOption->value.'_count'} }}</td>
                                    @endforeach
                                    <td>{{ (int) $row->total_count }}</td>
                                    <td>{{ $row->total_count > 0 ? number_format($row->present_count / $row->total_count * 100, 0) : 0 }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $recap->links('admin.partials.pagination') }}
            @endif
        @elseif ($records->isEmpty())
            @if ($hasFilters)
                <x-admin.empty-state title="Absensi tidak ditemukan" icon="search">
                    Tidak ada catatan absensi yang cocok dengan pencarian atau filter Anda.
                </x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada catatan absensi" icon="attendance">
                    Catat kehadiran murid melalui tombol Catat Absensi.
                </x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap" style="margin-top: 1rem;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Murid</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Jadwal / Sesi</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Status Kehadiran</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td class="is-muted">{{ $records->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="admin-cell-name">{{ $record->student->name }}</span>
                                    @if ($record->notes)
                                        <span class="admin-cell-sub">{{ $record->notes }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $record->schedule->classroom->name }}
                                    <span class="admin-cell-sub">Guru: {{ $record->schedule->classroom->teacher?->name ?? 'belum ditentukan' }}</span>
                                </td>
                                <td>{{ $record->schedule->summary() }}</td>
                                <td>{{ $record->session_date->translatedFormat('d M Y') }}</td>
                                <td>
                                    <span class="admin-badge {{ $record->status->badgeClass() }}">{{ $record->status->label() }}</span>
                                    @if ($record->wasCorrected())
                                        <span class="admin-cell-sub">Dikoreksi dari {{ $record->previous_status?->label() }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.attendances.students.edit', $record) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Koreksi absensi {{ $record->student->name }} {{ $record->session_date->format('d-m-Y') }}">
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
                <p class="admin-result-info">Menampilkan {{ $records->total() }} catatan absensi.</p>
            @endif
        @endif
    </section>
@endsection
