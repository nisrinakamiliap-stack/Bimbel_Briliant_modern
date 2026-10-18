@extends('layouts.admin')

@section('title', 'Jadwal Belajar')
@section('page_title', 'Jadwal Belajar')

@php
$hasFilters = $search !== '' || $classroomId || $teacherId || $day || $status;
$todayWeekday = \App\Weekday::fromDate(now());
@endphp

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Jadwal Belajar</h2>
            <p>Atur jadwal mingguan setiap kelas. Bentrok jadwal kelas dan guru diperiksa otomatis.</p>
        </div>
        <div class="admin-actions">
            <nav class="admin-view-switch" aria-label="Pilihan tampilan jadwal">
                <a href="{{ request()->fullUrlWithQuery(['tampilan' => 'tabel', 'page' => null]) }}" class="{{ $viewMode === 'tabel' ? 'is-active' : '' }}" @if ($viewMode === 'tabel') aria-current="page" @endif>Tabel</a>
                <a href="{{ request()->fullUrlWithQuery(['tampilan' => 'kalender', 'page' => null]) }}" class="{{ $viewMode === 'kalender' ? 'is-active' : '' }}" @if ($viewMode === 'kalender') aria-current="page" @endif>Kalender</a>
            </nav>
            <a href="{{ route('admin.schedules.create') }}" class="admin-btn admin-btn-primary" id="btn-add-schedule">
                <x-admin.icon name="plus" :size="16" />
                Tambah Jadwal
            </a>
        </div>
    </div>

    <section class="admin-panel" aria-labelledby="schedules-heading">
        <h3 id="schedules-heading" class="sr-only">Daftar jadwal belajar</h3>

        <form action="{{ route('admin.schedules.index') }}" method="GET" class="admin-toolbar is-stacked" role="search">
            <input type="hidden" name="tampilan" value="{{ $viewMode }}">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama kelas atau nama guru</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $search }}" class="admin-input" placeholder="Cari nama/kode kelas atau nama guru..." maxlength="100">
            </div>
            <div class="admin-toolbar-row">
                <label for="kelas" class="sr-only">Filter kelas</label>
                <select id="kelas" name="kelas" class="admin-select">
                    <option value="">Semua kelas</option>
                    @foreach ($classrooms as $classroomOption)
                        <option value="{{ $classroomOption->id }}" @selected($classroomId === $classroomOption->id)>{{ $classroomOption->name }}</option>
                    @endforeach
                </select>
                <label for="guru" class="sr-only">Filter guru</label>
                <select id="guru" name="guru" class="admin-select">
                    <option value="">Semua guru</option>
                    @foreach ($teachers as $teacherOption)
                        <option value="{{ $teacherOption->id }}" @selected($teacherId === $teacherOption->id)>{{ $teacherOption->name }}</option>
                    @endforeach
                </select>
                <label for="hari" class="sr-only">Filter hari</label>
                <select id="hari" name="hari" class="admin-select">
                    <option value="">Semua hari</option>
                    @foreach ($weekdays as $weekday)
                        <option value="{{ $weekday->value }}" @selected($day === $weekday)>{{ $weekday->label() }}</option>
                    @endforeach
                </select>
                <label for="status" class="sr-only">Filter status</label>
                <select id="status" name="status" class="admin-select">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($status === 'nonaktif')>Tidak Aktif</option>
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.schedules.index', ['tampilan' => $viewMode]) }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($schedules->isEmpty() && $viewMode === 'tabel')
            @if ($hasFilters)
                <x-admin.empty-state title="Jadwal tidak ditemukan" icon="search">
                    Tidak ada jadwal yang cocok dengan pencarian atau filter Anda. Coba kata kunci lain atau reset filter.
                </x-admin.empty-state>
            @elseif ($classrooms->isEmpty())
                <x-admin.empty-state title="Belum ada kelas" icon="classes">
                    Jadwal dibuat untuk sebuah kelas. Tambahkan kelas terlebih dahulu melalui menu Kelas.
                </x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada jadwal" icon="schedule">
                    Tambahkan jadwal pertama untuk menentukan hari dan jam belajar setiap kelas.
                </x-admin.empty-state>
            @endif
        @elseif ($viewMode === 'kalender')
            @php $schedulesByDay = $schedules->groupBy(fn ($schedule) => $schedule->day_of_week->value); @endphp
            <div class="admin-calendar" id="schedule-calendar">
                @foreach ($weekdays as $weekday)
                    @php $daySchedules = $schedulesByDay->get($weekday->value, collect()); @endphp
                    <section class="admin-calendar-day {{ $weekday === $todayWeekday ? 'is-today' : '' }}" aria-labelledby="calendar-day-{{ $weekday->value }}">
                        <h4 class="admin-calendar-day-header" id="calendar-day-{{ $weekday->value }}" style="margin: 0;">
                            {{ $weekday->label() }}
                            <span>{{ $daySchedules->count() }} jadwal</span>
                        </h4>
                        @if ($daySchedules->isEmpty())
                            <p class="admin-calendar-empty">Tidak ada jadwal</p>
                        @else
                            <ul class="admin-calendar-list">
                                @foreach ($daySchedules as $schedule)
                                    <li class="admin-calendar-item {{ $schedule->is_active ? '' : 'is-inactive' }}">
                                        <a href="{{ route('admin.schedules.show', $schedule) }}">
                                            <span class="admin-calendar-time">{{ $schedule->timeRange() }}{{ $schedule->is_active ? '' : ' · Tidak aktif' }}</span>
                                            <span class="admin-calendar-title">{{ $schedule->classroom->name }}</span>
                                            <span class="admin-calendar-meta">{{ $schedule->classroom->teacher?->name ?? 'Guru belum ditentukan' }}</span>
                                            @if ($schedule->room)
                                                <span class="admin-calendar-meta">{{ $schedule->room }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endforeach
            </div>
            <p class="admin-result-info">Menampilkan {{ $schedules->count() }} jadwal dalam seminggu. Jadwal tidak aktif ditampilkan lebih pudar.</p>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Kelas</th>
                            <th scope="col">Program Bimbel</th>
                            <th scope="col">Guru Pengajar</th>
                            <th scope="col">Hari</th>
                            <th scope="col">Jam Mulai</th>
                            <th scope="col">Jam Selesai</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                            <tr>
                                <td class="is-muted">{{ $schedules->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="admin-cell-name">{{ $schedule->classroom->name }}</span>
                                    <span class="admin-cell-sub">{{ $schedule->room ?? 'Ruangan belum diisi' }}</span>
                                </td>
                                <td>{{ $schedule->classroom->program->name }}</td>
                                <td>
                                    @if ($schedule->classroom->teacher)
                                        {{ $schedule->classroom->teacher->name }}
                                    @else
                                        <span class="admin-badge is-pending">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td>{{ $schedule->day_of_week->label() }}</td>
                                <td>{{ substr($schedule->start_time, 0, 5) }}</td>
                                <td>{{ substr($schedule->end_time, 0, 5) }}</td>
                                <td>
                                    @if ($schedule->is_active)
                                        <span class="admin-badge is-success">Aktif</span>
                                    @else
                                        <span class="admin-badge is-neutral">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('admin.schedules.show', $schedule) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Detail jadwal {{ $schedule->classroom->name }} {{ $schedule->summary() }}">
                                            <x-admin.icon name="eye" :size="14" /> Detail
                                        </a>
                                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Edit jadwal {{ $schedule->classroom->name }} {{ $schedule->summary() }}">
                                            <x-admin.icon name="edit" :size="14" /> Edit
                                        </a>
                                        @include('admin.schedules._toggle-status', ['schedule' => $schedule, 'isSmall' => true])
                                        <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST"
                                              data-confirm="Hapus jadwal {{ $schedule->classroom->name }} pada {{ $schedule->summary() }} secara permanen? Jika jadwal sudah memiliki riwayat, penghapusan akan ditolak.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn admin-btn-danger-outline admin-btn-sm" aria-label="Hapus jadwal {{ $schedule->classroom->name }} {{ $schedule->summary() }}">
                                                <x-admin.icon name="trash" :size="14" /> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($schedules->hasPages())
                {{ $schedules->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $schedules->total() }} jadwal.</p>
            @endif
        @endif
    </section>
@endsection
