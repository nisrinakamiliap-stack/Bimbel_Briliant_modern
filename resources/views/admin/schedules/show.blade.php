@extends('layouts.admin')

@section('title', 'Detail Jadwal — '.$schedule->classroom->name)
@section('page_title', 'Jadwal Belajar')

@php
$classroom = $schedule->classroom;
$teacher = $classroom->teacher;
@endphp

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.schedules.index') }}">Jadwal Belajar</a></li>
                <li aria-current="page">{{ $classroom->name }} · {{ $schedule->summary() }}</li>
            </ol>
            <h2>Detail Jadwal</h2>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.schedules.index') }}" class="admin-btn admin-btn-secondary">
                <x-admin.icon name="arrow-left" :size="16" />
                Kembali ke Daftar Jadwal
            </a>
            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="admin-btn admin-btn-primary">
                <x-admin.icon name="edit" :size="16" />
                Edit
            </a>
        </div>
    </div>

    <x-admin.flash />

    <div class="admin-grid">
        <section class="admin-panel is-wide" aria-labelledby="schedule-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="schedule-heading">{{ $schedule->day_of_week->label() }}, {{ $schedule->timeRange() }}</h3>
                    <p>{{ $classroom->name }}</p>
                </div>
                @if ($schedule->is_active)
                    <span class="admin-badge is-success">Aktif</span>
                @else
                    <span class="admin-badge is-neutral">Tidak Aktif</span>
                @endif
            </div>
            <dl class="admin-detail-list">
                <div>
                    <dt>Kelas</dt>
                    <dd>
                        <a href="{{ route('admin.classes.show', $classroom) }}" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;">{{ $classroom->name }}</a>
                        @if ($classroom->code)
                            <span class="admin-cell-sub">{{ $classroom->code }}</span>
                        @endif
                        @unless ($classroom->is_active)
                            <span class="admin-badge is-neutral">Kelas tidak aktif</span>
                        @endunless
                    </dd>
                </div>
                <div>
                    <dt>Program Bimbel</dt>
                    <dd>{{ $classroom->program->name }}</dd>
                </div>
                <div>
                    <dt>Guru Pengajar</dt>
                    <dd>
                        @if ($teacher === null)
                            <span class="admin-badge is-pending">Belum ditentukan</span>
                        @elseif ($teacher->trashed())
                            {{ $teacher->name }} <span class="admin-badge is-danger">Akun dihapus</span>
                        @else
                            <a href="{{ route('admin.teachers.show', $teacher) }}" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;">{{ $teacher->name }}</a>
                            @if ($teacher->teacherProfile?->subject)
                                <span class="admin-cell-sub">{{ $teacher->teacherProfile->subject }}</span>
                            @endif
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Hari &amp; Jam</dt>
                    <dd>{{ $schedule->day_of_week->label() }}, {{ $schedule->timeRange() }}</dd>
                </div>
                <div>
                    <dt>Masa Berlaku</dt>
                    <dd>
                        @if ($schedule->starts_on === null && $schedule->ends_on === null)
                            Berlaku setiap minggu tanpa batas waktu
                        @else
                            {{ $schedule->starts_on?->translatedFormat('d F Y') ?? 'Sejak awal' }} – {{ $schedule->ends_on?->translatedFormat('d F Y') ?? 'tanpa batas' }}
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Ruangan / Lokasi</dt>
                    <dd>{{ $schedule->room ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Mode Pembelajaran</dt>
                    <dd>{{ $classroom->learning_mode->label() }}</dd>
                </div>
                <div>
                    <dt>Pertemuan Berikutnya</dt>
                    <dd>{{ $schedule->nextOccurrence(now())?->translatedFormat('l, d F Y · H:i') ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="admin-panel" aria-labelledby="status-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="status-heading">Status Jadwal</h3>
                    <p>{{ $schedule->is_active ? 'Jadwal aktif dan diperhitungkan dalam pemeriksaan bentrok.' : 'Jadwal tidak aktif. Saat diaktifkan, bentrok akan diperiksa ulang.' }}</p>
                </div>
                @include('admin.schedules._toggle-status', ['schedule' => $schedule, 'isSmall' => false])
            </div>
        </section>

        <section class="admin-panel" aria-labelledby="danger-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="danger-heading">Hapus Jadwal</h3>
                    @if ($isDeletable)
                        <p>Jadwal belum memiliki riwayat absensi sehingga aman dihapus.</p>
                    @else
                        <p>Jadwal sudah memiliki riwayat dan tidak dapat dihapus. Nonaktifkan jadwal ini.</p>
                    @endif
                </div>
                @if ($isDeletable)
                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST"
                          data-confirm="Hapus jadwal {{ $classroom->name }} pada {{ $schedule->summary() }} secara permanen? Tindakan ini tidak dapat dibatalkan.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-danger-outline" id="btn-delete-schedule">
                            <x-admin.icon name="trash" :size="16" />
                            Hapus
                        </button>
                    </form>
                @endif
            </div>
        </section>
    </div>
@endsection
