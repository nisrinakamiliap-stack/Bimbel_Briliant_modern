@extends('layouts.teacher')

@section('title', $classroom->name)

@section('content')
    <x-admin.flash />

    <nav aria-label="Breadcrumb">
        <ol class="admin-breadcrumb">
            <li><a href="{{ route('teacher.classes.index') }}">Kelas Saya</a></li>
            <li aria-current="page">{{ $classroom->name }}</li>
        </ol>
    </nav>

    <div class="teacher-page-head">
        <div>
            <h1>{{ $classroom->name }}</h1>
            <p>{{ $classroom->program->name }} · {{ $classroom->learning_mode->label() }} · Kode {{ $classroom->code }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('teacher.learning.materials.create', ['kelas' => $classroom->id]) }}" class="admin-btn admin-btn-secondary">Tambah Materi</a>
            <a href="{{ route('teacher.learning.assignments.create', ['kelas' => $classroom->id]) }}" class="admin-btn admin-btn-primary">Buat Tugas</a>
        </div>
    </div>

    <div class="teacher-columns">
        <section class="admin-panel" aria-labelledby="students-heading">
            <div class="admin-panel-header">
                <div>
                    <h2 id="students-heading">Murid Terdaftar</h2>
                    <p>{{ $enrollments->count() }} murid dengan pendaftaran diterima. Rekap kehadiran dihitung dari sesi yang sudah dicatat.</p>
                </div>
            </div>
            @if ($enrollments->isEmpty())
                <x-admin.empty-state title="Belum ada murid" icon="students">
                    Murid muncul di sini setelah pendaftarannya ke kelas ini diterima admin.
                </x-admin.empty-state>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Nama Murid</th>
                                @foreach ($statuses as $status)
                                    <th scope="col">{{ $status->label() }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($enrollments as $enrollment)
                                @php $counts = $attendanceCounts->get($enrollment->student_id, collect()); @endphp
                                <tr>
                                    <td>
                                        <span class="admin-cell-name">{{ $enrollment->student->name }}</span>
                                        <span class="admin-cell-sub">Terdaftar {{ $enrollment->enrolled_on->translatedFormat('d M Y') }}</span>
                                    </td>
                                    @foreach ($statuses as $status)
                                        <td style="font-variant-numeric: tabular-nums;">{{ $counts->get($status->value, 0) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="teacher-stack">
            <section class="admin-panel" aria-labelledby="class-schedules-heading">
                <div class="admin-panel-header">
                    <div>
                        <h2 id="class-schedules-heading">Jadwal Belajar</h2>
                    </div>
                    <a href="{{ route('teacher.schedules.index', ['kelas' => $classroom->id]) }}" class="teacher-link">Lihat di jadwal</a>
                </div>
                @if ($classroom->schedules->isEmpty())
                    <x-admin.empty-state title="Belum ada jadwal" icon="schedule">Jadwal kelas dibuat oleh admin.</x-admin.empty-state>
                @else
                    <ul class="teacher-list">
                        @foreach ($classroom->schedules as $schedule)
                            <li class="teacher-list-item">
                                <div>
                                    <strong>{{ $schedule->summary() }}</strong>
                                    <p>{{ $schedule->room ?: 'Ruangan belum ditentukan' }}</p>
                                </div>
                                @unless ($schedule->is_active)
                                    <span class="admin-badge is-neutral">Nonaktif</span>
                                @endunless
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="admin-panel" aria-labelledby="class-materials-heading">
                <div class="admin-panel-header">
                    <div>
                        <h2 id="class-materials-heading">Materi Terbaru</h2>
                    </div>
                    <a href="{{ route('teacher.learning.materials.index', ['kelas' => $classroom->id]) }}" class="teacher-link">Semua materi</a>
                </div>
                @if ($classroom->materials->isEmpty())
                    <x-admin.empty-state title="Belum ada materi" icon="learning">Tambahkan materi agar murid dapat belajar sebelum kelas.</x-admin.empty-state>
                @else
                    <ul class="teacher-list">
                        @foreach ($classroom->materials as $material)
                            <li class="teacher-list-item">
                                <a href="{{ route('teacher.learning.materials.show', $material) }}">{{ $material->title }}</a>
                                <span class="admin-badge {{ $material->status->badgeClass() }}">{{ $material->status->label() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="admin-panel" aria-labelledby="class-assignments-heading">
                <div class="admin-panel-header">
                    <div>
                        <h2 id="class-assignments-heading">Tugas Terbaru</h2>
                    </div>
                    <a href="{{ route('teacher.learning.assignments.index', ['kelas' => $classroom->id]) }}" class="teacher-link">Semua tugas</a>
                </div>
                @if ($classroom->assignments->isEmpty())
                    <x-admin.empty-state title="Belum ada tugas" icon="edit">Tugas yang Anda buat untuk kelas ini akan tampil di sini.</x-admin.empty-state>
                @else
                    <ul class="teacher-list">
                        @foreach ($classroom->assignments as $assignment)
                            <li class="teacher-list-item">
                                <div>
                                    <a href="{{ route('teacher.learning.assignments.show', $assignment) }}">{{ $assignment->title }}</a>
                                    <p>Tenggat {{ $assignment->due_at->translatedFormat('d M Y, H:i') }} · {{ $assignment->submissions_count }} pengumpulan</p>
                                </div>
                                <span class="admin-badge {{ $assignment->status->badgeClass() }}">{{ $assignment->status->label() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endsection
