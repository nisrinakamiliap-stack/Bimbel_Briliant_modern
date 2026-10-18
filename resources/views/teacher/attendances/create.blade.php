@extends('layouts.teacher')

@section('title', 'Catat Absensi Murid')

@section('content')
    <nav aria-label="Breadcrumb">
        <ol class="admin-breadcrumb">
            <li><a href="{{ route('teacher.attendances.index') }}">Absensi</a></li>
            <li aria-current="page">Catat Absensi</li>
        </ol>
    </nav>

    <div class="teacher-page-head">
        <div>
            <h1>Catat Absensi Murid</h1>
            <p>Pilih tanggal dan sesi kelas Anda, lalu tandai kehadiran setiap murid. Catatan yang sudah tersimpan hanya dapat dikoreksi oleh admin.</p>
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
        <form action="{{ route('teacher.attendances.create') }}" method="GET" class="admin-toolbar is-stacked" id="form-select-session">
            <div class="admin-toolbar-row">
                <div style="flex: 1; min-width: 12rem;">
                    <label for="tanggal" class="admin-label">Tanggal sesi</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ $date->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="admin-input">
                </div>
                <div style="flex: 2; min-width: 14rem;">
                    <label for="jadwal" class="admin-label">Sesi pada {{ $date->translatedFormat('l, d F Y') }}</label>
                    <select id="jadwal" name="jadwal" class="admin-select" @disabled($schedules->isEmpty())>
                        <option value="">Pilih sesi</option>
                        @foreach ($schedules as $scheduleOption)
                            <option value="{{ $scheduleOption->id }}" @selected($selectedSchedule?->id === $scheduleOption->id)>
                                {{ $scheduleOption->timeRange() }} · {{ $scheduleOption->classroom->name }}
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
            <x-admin.empty-state title="Tidak ada sesi pada tanggal ini" icon="schedule">
                Anda tidak memiliki jadwal mengajar pada {{ $date->translatedFormat('l, d F Y') }}. Pilih tanggal lain.
            </x-admin.empty-state>
        @elseif ($invalidScheduleSelected)
            <x-admin.empty-state title="Sesi tidak tersedia" icon="alert">
                Sesi yang dipilih bukan milik kelas Anda atau tidak berlangsung pada tanggal tersebut. Pilih sesi dari daftar.
            </x-admin.empty-state>
        @elseif ($selectedSchedule === null)
            <x-admin.empty-state title="Pilih sesi" icon="schedule">
                Pilih salah satu sesi untuk menampilkan murid peserta kelas.
            </x-admin.empty-state>
        @elseif (! $hasStarted)
            <x-admin.empty-state title="Sesi belum dimulai" icon="clock">
                Absensi dapat dicatat mulai pukul {{ $selectedSchedule->sessionStartsAt($date)->format('H:i') }}.
            </x-admin.empty-state>
        @elseif ($roster->isEmpty())
            <x-admin.empty-state title="Belum ada murid di kelas ini" icon="students">
                Kelas {{ $selectedSchedule->classroom->name }} belum memiliki murid dengan pendaftaran diterima pada tanggal ini.
            </x-admin.empty-state>
        @else
            @php $pendingCount = $roster->reject(fn ($enrollment) => $existingRecords->has($enrollment->student_id))->count(); @endphp
            <form action="{{ route('teacher.attendances.store') }}" method="POST" class="admin-form" id="form-record-attendance">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $selectedSchedule->id }}">
                <input type="hidden" name="session_date" value="{{ $date->format('Y-m-d') }}">

                <div class="admin-form-section">
                    <h2>{{ $selectedSchedule->classroom->name }}, {{ $selectedSchedule->timeRange() }}</h2>
                    <p>{{ $roster->count() }} murid peserta · {{ $roster->count() - $pendingCount }} sudah tercatat · {{ $pendingCount }} belum tercatat.</p>

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
                                        <td class="admin-cell-name">{{ $enrollment->student->name }}</td>
                                        @if ($existing)
                                            <td>
                                                <span class="admin-badge {{ $existing->status->badgeClass() }}">{{ $existing->status->label() }}</span>
                                                <span class="admin-cell-sub">Sudah tercatat</span>
                                            </td>
                                            <td>{{ $existing->notes ?? 'Tanpa keterangan' }}</td>
                                        @else
                                            <td>
                                                <fieldset class="teacher-radio-group">
                                                    <legend class="sr-only">Status kehadiran {{ $enrollment->student->name }}</legend>
                                                    @foreach ($statuses as $statusOption)
                                                        <label>
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
                                                       value="{{ old("attendances.{$enrollment->id}.notes") }}" placeholder="Opsional, misalnya alasan izin">
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <a href="{{ route('teacher.attendances.index') }}" class="admin-btn admin-btn-secondary">Kembali</a>
                    <button type="submit" class="admin-btn admin-btn-primary" @disabled($pendingCount === 0) id="btn-save-attendance" data-loading-label="Menyimpan...">Simpan Absensi</button>
                </div>
            </form>
        @endif
    </section>
@endsection
