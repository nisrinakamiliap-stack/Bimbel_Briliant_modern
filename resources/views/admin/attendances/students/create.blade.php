@extends('layouts.admin')

@section('title', 'Catat Absensi Murid')
@section('page_title', 'Absensi')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.attendances.students.index') }}">Absensi Murid</a></li>
                <li aria-current="page">Catat Absensi</li>
            </ol>
            <h2>Catat Absensi Murid</h2>
            <p>Pilih tanggal, lalu jadwal yang berlangsung, kemudian isi status kehadiran setiap murid peserta kelas.</p>
        </div>
    </div>

    <x-admin.flash />

    @foreach (['schedule_id', 'session_date', 'attendances', 'tanggal', 'jadwal'] as $errorKey)
        @error($errorKey)
            <div class="admin-alert is-error" role="alert">
                <x-admin.icon name="alert" :size="18" />
                <span>{{ $message }}</span>
            </div>
        @enderror
    @endforeach

    <section class="admin-panel">
        <form action="{{ route('admin.attendances.students.create') }}" method="GET" class="admin-toolbar is-stacked" id="form-select-session">
            <div class="admin-toolbar-row">
                <div style="flex: 1; min-width: 12rem;">
                    <label for="tanggal" class="admin-label">1. Tanggal Sesi</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ $date->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="admin-input">
                </div>
                <div style="flex: 2; min-width: 16rem;">
                    <label for="jadwal" class="admin-label">2. Jadwal yang Berlangsung ({{ $date->translatedFormat('l, d F Y') }})</label>
                    <select id="jadwal" name="jadwal" class="admin-select" @disabled($schedules->isEmpty())>
                        <option value="">— Pilih jadwal —</option>
                        @foreach ($schedules as $scheduleOption)
                            <option value="{{ $scheduleOption->id }}" @selected($selectedSchedule?->id === $scheduleOption->id)>
                                {{ $scheduleOption->timeRange() }} · {{ $scheduleOption->classroom->name }} · Guru: {{ $scheduleOption->classroom->teacher?->name ?? 'belum ditentukan' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="align-self: flex-end;">
                    <button type="submit" class="admin-btn admin-btn-secondary">Tampilkan Murid</button>
                </div>
            </div>
        </form>

        @if ($schedules->isEmpty())
            <x-admin.empty-state title="Tidak ada jadwal pada tanggal ini" icon="schedule">
                Tidak ada jadwal aktif yang berlangsung pada {{ $date->translatedFormat('l, d F Y') }}. Pilih tanggal lain.
            </x-admin.empty-state>
        @elseif ($invalidScheduleSelected)
            <x-admin.empty-state title="Jadwal tidak berlangsung pada tanggal ini" icon="alert">
                Jadwal yang dipilih tidak berlangsung pada tanggal tersebut. Pilih jadwal dari daftar.
            </x-admin.empty-state>
        @elseif ($selectedSchedule === null)
            <x-admin.empty-state title="Pilih jadwal" icon="schedule">
                Pilih salah satu jadwal untuk menampilkan murid peserta kelas.
            </x-admin.empty-state>
        @elseif (! $hasStarted)
            <x-admin.empty-state title="Sesi belum dimulai" icon="clock">
                Absensi dapat dicatat mulai pukul {{ $selectedSchedule->sessionStartsAt($date)->format('H:i') }}.
            </x-admin.empty-state>
        @elseif ($roster->isEmpty())
            <x-admin.empty-state title="Belum ada murid di kelas ini" icon="students">
                Kelas {{ $selectedSchedule->classroom->name }} belum memiliki murid dengan pendaftaran Diterima pada tanggal ini.
            </x-admin.empty-state>
        @else
            @php $pendingCount = $roster->reject(fn ($enrollment) => $existingRecords->has($enrollment->student_id))->count(); @endphp
            <form action="{{ route('admin.attendances.students.store') }}" method="POST" class="admin-form" id="form-record-attendance">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $selectedSchedule->id }}">
                <input type="hidden" name="session_date" value="{{ $date->format('Y-m-d') }}">

                <div class="admin-form-section">
                    <h3>3. Kehadiran — {{ $selectedSchedule->classroom->name }}, {{ $selectedSchedule->timeRange() }}</h3>
                    <p>{{ $roster->count() }} murid peserta · {{ $roster->count() - $pendingCount }} sudah tercatat (ubah melalui Koreksi) · {{ $pendingCount }} belum tercatat.</p>

                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th scope="col">Murid</th>
                                    <th scope="col">Status Kehadiran</th>
                                    <th scope="col">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roster as $enrollment)
                                    @php $existing = $existingRecords->get($enrollment->student_id); @endphp
                                    <tr>
                                        <td>
                                            <span class="admin-cell-name">{{ $enrollment->student->name }}</span>
                                            <span class="admin-cell-sub">{{ $enrollment->student->email }}</span>
                                        </td>
                                        @if ($existing)
                                            <td>
                                                <span class="admin-badge {{ $existing->status->badgeClass() }}">{{ $existing->status->label() }}</span>
                                                <span class="admin-cell-sub">Sudah tercatat</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.attendances.students.edit', $existing) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Koreksi</a>
                                            </td>
                                        @else
                                            <td>
                                                <fieldset style="border: 0; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: .75rem;">
                                                    <legend class="sr-only">Status kehadiran {{ $enrollment->student->name }}</legend>
                                                    @foreach ($statuses as $statusOption)
                                                        <label style="display: inline-flex; align-items: center; gap: .3rem; font-size: .875rem; cursor: pointer;">
                                                            <input type="radio" name="attendances[{{ $enrollment->id }}][status]" value="{{ $statusOption->value }}"
                                                                   @checked(old("attendances.{$enrollment->id}.status", 'present') === $statusOption->value) required>
                                                            {{ $statusOption->label() }}
                                                        </label>
                                                    @endforeach
                                                </fieldset>
                                            </td>
                                            <td>
                                                <label for="notes-{{ $enrollment->id }}" class="sr-only">Keterangan {{ $enrollment->student->name }}</label>
                                                <input type="text" id="notes-{{ $enrollment->id }}" name="attendances[{{ $enrollment->id }}][notes]" class="admin-input" maxlength="255"
                                                       value="{{ old("attendances.{$enrollment->id}.notes") }}" placeholder="Opsional">
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <a href="{{ route('admin.attendances.students.index') }}" class="admin-btn admin-btn-secondary">Kembali</a>
                    <button type="submit" class="admin-btn admin-btn-primary" @disabled($pendingCount === 0) id="btn-save-attendance">
                        <x-admin.icon name="check" :size="16" />
                        Simpan Absensi
                    </button>
                </div>
            </form>
        @endif
    </section>
@endsection
