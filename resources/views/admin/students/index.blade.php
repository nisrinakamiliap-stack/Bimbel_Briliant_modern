@extends('layouts.admin')

@section('title', 'Data Murid')
@section('page_title', 'Manajemen Murid')

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Data Murid</h2>
            <p>Kelola akun dan profil murid Bimbel Sentra Edukasi.</p>
        </div>
        <a href="{{ route('admin.students.create') }}" class="admin-btn admin-btn-primary" id="btn-add-student">
            <x-admin.icon name="plus" :size="16" />
            Tambah Murid
        </a>
    </div>

    @include('admin.students.partials.tabs')

    <section class="admin-panel" aria-labelledby="students-heading">
        <h3 id="students-heading" class="sr-only">Daftar murid</h3>

        <form action="{{ route('admin.students.index') }}" method="GET" class="admin-toolbar" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama atau email murid</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $search }}" class="admin-input" placeholder="Cari nama atau email..." maxlength="100">
            </div>
            <div class="admin-toolbar-row">
                <label for="jenjang" class="sr-only">Filter jenjang pendidikan</label>
                <select id="jenjang" name="jenjang" class="admin-select">
                    <option value="">Semua jenjang</option>
                    @foreach ($educationLevels as $level)
                        <option value="{{ $level->value }}" @selected($educationLevel === $level->value)>{{ $level->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($search !== '' || $educationLevel)
                    <a href="{{ route('admin.students.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($students->isEmpty())
            @if ($search !== '' || $educationLevel)
                <x-admin.empty-state title="Murid tidak ditemukan" icon="search">
                    Tidak ada murid yang cocok dengan pencarian atau filter Anda. Coba kata kunci lain atau reset filter.
                </x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada data murid" icon="students">
                    Murid yang mendaftar melalui website atau ditambahkan oleh admin akan tampil di sini.
                </x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Murid</th>
                            <th scope="col">Email</th>
                            <th scope="col">No. Telepon</th>
                            <th scope="col">Jenjang</th>
                            <th scope="col">Terdaftar</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td class="is-muted">{{ $students->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="admin-cell-name">{{ $student->name }}</span>
                                    @if ($student->studentProfile?->school_name)
                                        <span class="admin-cell-sub">{{ $student->studentProfile->school_name }}</span>
                                    @endif
                                </td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->phone ?? '—' }}</td>
                                <td>
                                    @if ($student->studentProfile?->education_level)
                                        <span class="admin-badge is-success">{{ $student->studentProfile->education_level->label() }}</span>
                                    @else
                                        <span class="admin-badge is-neutral">Belum diisi</span>
                                    @endif
                                </td>
                                <td class="is-muted">{{ $student->created_at?->translatedFormat('d M Y') }}</td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('admin.students.show', $student) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Detail {{ $student->name }}">
                                            <x-admin.icon name="eye" :size="14" /> Detail
                                        </a>
                                        <a href="{{ route('admin.students.edit', $student) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Edit {{ $student->name }}">
                                            <x-admin.icon name="edit" :size="14" /> Edit
                                        </a>
                                        <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
                                              data-confirm="Hapus murid {{ $student->name }}? Akun tidak dapat digunakan untuk login lagi, tetapi datanya tetap tersimpan sebagai arsip.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn admin-btn-danger-outline admin-btn-sm" aria-label="Hapus {{ $student->name }}">
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

            @if ($students->hasPages())
                {{ $students->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $students->total() }} murid.</p>
            @endif
        @endif
    </section>
@endsection
