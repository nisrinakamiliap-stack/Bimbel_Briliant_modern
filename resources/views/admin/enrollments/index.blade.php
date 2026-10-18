@extends('layouts.admin')

@section('title', $isHistory ? 'Riwayat Pendaftaran' : 'Pendaftaran Murid')
@section('page_title', 'Manajemen Murid')

@php
$hasFilters = $search !== '' || $programId || $classroomId || $status || $dateFrom || $dateTo;
@endphp

@section('content')
    <x-admin.flash />

    @foreach (['status', 'student_id', 'classroom_id'] as $statusErrorKey)
        @error($statusErrorKey)
            <div class="admin-alert is-error" role="alert">
                <x-admin.icon name="alert" :size="18" />
                <span>{{ $message }}</span>
            </div>
        @enderror
    @endforeach

    <div class="admin-page-header">
        <div>
            <h2>{{ $isHistory ? 'Riwayat Pendaftaran' : 'Pendaftaran Murid' }}</h2>
            <p>{{ $isHistory ? 'Pendaftaran yang sudah ditolak atau dibatalkan. Data tetap tersimpan dan dapat dibuka kembali.' : 'Kelola pendaftaran murid ke program dan kelas, verifikasi, serta riwayat statusnya.' }}</p>
        </div>
        <a href="{{ route('admin.enrollments.create') }}" class="admin-btn admin-btn-primary" id="btn-add-enrollment">
            <x-admin.icon name="plus" :size="16" />
            Tambah Pendaftaran
        </a>
    </div>

    @include('admin.students.partials.tabs')

    <section class="admin-panel" aria-labelledby="enrollments-heading">
        <h3 id="enrollments-heading" class="sr-only">Daftar pendaftaran</h3>

        <form action="{{ route('admin.enrollments.index') }}" method="GET" class="admin-toolbar is-stacked" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama atau email murid</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $search }}" class="admin-input" placeholder="Cari nama atau email murid..." maxlength="100">
            </div>
            <div class="admin-toolbar-row">
                <label for="program" class="sr-only">Filter program</label>
                <select id="program" name="program" class="admin-select">
                    <option value="">Semua program</option>
                    @foreach ($programs as $programOption)
                        <option value="{{ $programOption->id }}" @selected($programId === $programOption->id)>{{ $programOption->name }}</option>
                    @endforeach
                </select>
                <label for="kelas" class="sr-only">Filter kelas</label>
                <select id="kelas" name="kelas" class="admin-select">
                    <option value="">Semua kelas</option>
                    @foreach ($classrooms as $classroomOption)
                        <option value="{{ $classroomOption->id }}" @selected($classroomId === $classroomOption->id)>{{ $classroomOption->name }}</option>
                    @endforeach
                </select>
                <label for="status" class="sr-only">Filter status</label>
                <select id="status" name="status" class="admin-select">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected($status === $statusOption)>{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-toolbar-row">
                <label for="dari" class="sr-only">Tanggal pendaftaran dari</label>
                <input type="date" id="dari" name="dari" value="{{ $dateFrom }}" class="admin-input" style="flex: 1; min-width: 10rem;" title="Tanggal pendaftaran dari">
                <label for="sampai" class="sr-only">Tanggal pendaftaran sampai</label>
                <input type="date" id="sampai" name="sampai" value="{{ $dateTo }}" class="admin-input" style="flex: 1; min-width: 10rem;" title="Tanggal pendaftaran sampai">
                @if ($isHistory)
                    <input type="hidden" name="riwayat" value="1">
                @endif
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.enrollments.index', $isHistory ? ['riwayat' => 1] : []) }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
            @error('sampai') <p class="admin-error" style="margin: 0;">{{ $message }}</p> @enderror
        </form>

        @if ($enrollments->isEmpty())
            @if ($hasFilters)
                <x-admin.empty-state title="Pendaftaran tidak ditemukan" icon="search">
                    Tidak ada pendaftaran yang cocok dengan pencarian atau filter Anda. Coba kata kunci lain atau reset filter.
                </x-admin.empty-state>
            @elseif ($isHistory)
                <x-admin.empty-state title="Belum ada riwayat pendaftaran" icon="enrollments">
                    Pendaftaran yang ditolak atau dibatalkan akan tampil di sini.
                </x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada pendaftaran" icon="enrollments">
                    Daftarkan murid ke program bimbel melalui tombol Tambah Pendaftaran.
                </x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Murid</th>
                            <th scope="col">Program Bimbel</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Tanggal Pendaftaran</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($enrollments as $enrollment)
                            <tr>
                                <td class="is-muted">{{ $enrollments->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="admin-cell-name">{{ $enrollment->student->name }}</span>
                                    <span class="admin-cell-sub">{{ $enrollment->student->email }}{{ $enrollment->student->trashed() ? ' · akun dihapus' : '' }}</span>
                                </td>
                                <td>{{ $enrollment->program->name }}</td>
                                <td>
                                    @if ($enrollment->classroom)
                                        {{ $enrollment->classroom->name }}
                                    @else
                                        <span class="admin-badge is-neutral">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td>{{ $enrollment->enrolled_on->translatedFormat('d M Y') }}</td>
                                <td><span class="admin-badge {{ $enrollment->status->badgeClass() }}">{{ $enrollment->status->label() }}</span></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Detail pendaftaran {{ $enrollment->student->name }}">
                                            <x-admin.icon name="eye" :size="14" /> Detail
                                        </a>
                                        <a href="{{ route('admin.enrollments.edit', $enrollment) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Edit pendaftaran {{ $enrollment->student->name }}">
                                            <x-admin.icon name="edit" :size="14" /> Edit
                                        </a>
                                        @include('admin.enrollments._status-actions', ['enrollment' => $enrollment, 'isSmall' => true])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($enrollments->hasPages())
                {{ $enrollments->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $enrollments->total() }} pendaftaran.</p>
            @endif
        @endif
    </section>
@endsection
