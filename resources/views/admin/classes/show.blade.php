@extends('layouts.admin')

@section('title', $classroom->name.' — Data Kelas')
@section('page_title', 'Data Kelas')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.classes.index') }}">Data Kelas</a></li>
                <li aria-current="page">{{ $classroom->name }}</li>
            </ol>
            <h2>Detail Kelas</h2>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.classes.index') }}" class="admin-btn admin-btn-secondary">
                <x-admin.icon name="arrow-left" :size="16" />
                Kembali
            </a>
            <a href="{{ route('admin.classes.edit', $classroom) }}" class="admin-btn admin-btn-primary">
                <x-admin.icon name="edit" :size="16" />
                Edit
            </a>
        </div>
    </div>

    <x-admin.flash />

    <div class="admin-grid">
        <section class="admin-panel is-wide" aria-labelledby="class-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="class-heading">{{ $classroom->name }}</h3>
                    <p>{{ $classroom->code ?? 'Tanpa kode kelas' }}</p>
                </div>
                @if ($classroom->is_active)
                    <span class="admin-badge is-success">Aktif</span>
                @else
                    <span class="admin-badge is-neutral">Tidak Aktif</span>
                @endif
            </div>
            <dl class="admin-detail-list">
                <div>
                    <dt>Program Bimbel</dt>
                    <dd>
                        <a href="{{ route('admin.programs.show', $classroom->program) }}" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;">{{ $classroom->program->name }}</a>
                        @unless ($classroom->program->is_active)
                            <span class="admin-badge is-neutral">Program tidak aktif</span>
                        @endunless
                    </dd>
                </div>
                <div>
                    <dt>Guru Pengajar</dt>
                    <dd>
                        @if ($classroom->teacher)
                            @if ($classroom->teacher->trashed())
                                {{ $classroom->teacher->name }} <span class="admin-badge is-danger">Akun dihapus</span>
                            @else
                                <a href="{{ route('admin.teachers.show', $classroom->teacher) }}" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;">{{ $classroom->teacher->name }}</a>
                                @if ($classroom->teacher->teacherProfile?->subject)
                                    <span class="admin-cell-sub">{{ $classroom->teacher->teacherProfile->subject }}</span>
                                @endif
                            @endif
                        @else
                            <span class="admin-badge is-pending">Belum ditentukan</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Kapasitas</dt>
                    <dd>
                        {{ $classroom->enrollments->unique('student_id')->count() }}/{{ $classroom->capacity }} murid terisi
                        @if ($classroom->enrollments->unique('student_id')->count() >= $classroom->capacity)
                            <span class="admin-badge is-danger">Penuh</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Mode Pembelajaran</dt>
                    <dd>{{ $classroom->learning_mode->label() }}</dd>
                </div>
                <div>
                    <dt>Tanggal Dibuat</dt>
                    <dd>{{ $classroom->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Terakhir Diperbarui</dt>
                    <dd>{{ $classroom->updated_at?->translatedFormat('d F Y, H:i') ?? '—' }}</dd>
                </div>
                <div style="grid-column: 1 / -1;">
                    <dt>Deskripsi</dt>
                    <dd>{{ $classroom->description ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="admin-panel is-wide" aria-labelledby="class-students-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="class-students-heading">Murid Terdaftar</h3>
                    <p>Pendaftaran aktif (menunggu verifikasi atau diterima) yang ditempatkan di kelas ini.</p>
                </div>
            </div>
            @if ($classroom->enrollments->isEmpty())
                <x-admin.empty-state title="Belum ada murid" icon="students">
                    Tempatkan murid ke kelas ini melalui menu Pendaftaran.
                </x-admin.empty-state>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Murid</th>
                                <th scope="col">Tanggal Pendaftaran</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classroom->enrollments as $classEnrollment)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.enrollments.show', $classEnrollment) }}" class="admin-cell-name" style="text-decoration: none;">{{ $classEnrollment->student->name }}</a>
                                        <span class="admin-cell-sub">{{ $classEnrollment->student->email }}</span>
                                    </td>
                                    <td>{{ $classEnrollment->enrolled_on->translatedFormat('d M Y') }}</td>
                                    <td><span class="admin-badge {{ $classEnrollment->status->badgeClass() }}">{{ $classEnrollment->status->label() }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-panel is-wide" aria-labelledby="class-schedules-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="class-schedules-heading">Jadwal Kelas</h3>
                    <p>Jadwal belajar mingguan kelas ini.</p>
                </div>
                <a href="{{ route('admin.schedules.create', ['kelas' => $classroom->id]) }}" class="admin-btn admin-btn-secondary admin-btn-sm">
                    <x-admin.icon name="plus" :size="14" /> Tambah Jadwal
                </a>
            </div>
            @if ($classroom->schedules->isEmpty())
                <x-admin.empty-state title="Belum ada jadwal" icon="schedule">
                    Kelas ini belum memiliki jadwal belajar.
                </x-admin.empty-state>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Hari</th>
                                <th scope="col">Jam</th>
                                <th scope="col">Ruangan</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classroom->schedules as $classSchedule)
                                <tr>
                                    <td><a href="{{ route('admin.schedules.show', $classSchedule) }}" class="admin-cell-name" style="text-decoration: none;">{{ $classSchedule->day_of_week->label() }}</a></td>
                                    <td>{{ $classSchedule->timeRange() }}</td>
                                    <td>{{ $classSchedule->room ?? '—' }}</td>
                                    <td>
                                        <span class="admin-badge {{ $classSchedule->is_active ? 'is-success' : 'is-neutral' }}">{{ $classSchedule->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-panel" aria-labelledby="status-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="status-heading">Status Kelas</h3>
                    <p>{{ $classroom->is_active ? 'Kelas aktif dan dapat menerima murid.' : 'Kelas tidak aktif, data tetap tersimpan.' }}</p>
                </div>
                @include('admin.classes._toggle-status', ['classroom' => $classroom, 'isSmall' => false])
            </div>
        </section>

        <section class="admin-panel" aria-labelledby="danger-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="danger-heading">Hapus Kelas</h3>
                    @if ($isDeletable)
                        <p>Kelas belum memiliki jadwal, pendaftaran, atau absensi sehingga aman dihapus.</p>
                    @else
                        <p>Kelas sudah memiliki riwayat dan tidak dapat dihapus. Nonaktifkan kelas ini.</p>
                    @endif
                </div>
                @if ($isDeletable)
                    <form action="{{ route('admin.classes.destroy', $classroom) }}" method="POST"
                          data-confirm="Hapus kelas {{ $classroom->name }} secara permanen? Tindakan ini tidak dapat dibatalkan.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-danger-outline" id="btn-delete-class">
                            <x-admin.icon name="trash" :size="16" />
                            Hapus
                        </button>
                    </form>
                @endif
            </div>
        </section>
    </div>
@endsection
