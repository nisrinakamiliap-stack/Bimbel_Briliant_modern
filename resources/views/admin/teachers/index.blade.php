@extends('layouts.admin')

@section('title', 'Data Guru')
@section('page_title', 'Data Guru')

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Data Guru</h2>
            <p>Kelola akun dan profil guru/tentor Bimbel Sentra Edukasi.</p>
        </div>
        <a href="{{ route('admin.teachers.create') }}" class="admin-btn admin-btn-primary" id="btn-add-teacher">
            <x-admin.icon name="plus" :size="16" />
            Tambah Guru
        </a>
    </div>

    <section class="admin-panel" aria-labelledby="teachers-heading">
        <h3 id="teachers-heading" class="sr-only">Daftar guru</h3>

        <form action="{{ route('admin.teachers.index') }}" method="GET" class="admin-toolbar" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama atau email guru</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $search }}" class="admin-input" placeholder="Cari nama atau email..." maxlength="100">
            </div>
            <div class="admin-toolbar-row">
                <label for="bidang" class="sr-only">Filter bidang keahlian</label>
                <select id="bidang" name="bidang" class="admin-select">
                    <option value="">Semua bidang keahlian</option>
                    @foreach ($subjects as $subjectOption)
                        <option value="{{ $subjectOption }}" @selected($subject === $subjectOption)>{{ $subjectOption }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($search !== '' || $subject !== '')
                    <a href="{{ route('admin.teachers.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($teachers->isEmpty())
            @if ($search !== '' || $subject !== '')
                <x-admin.empty-state title="Guru tidak ditemukan" icon="search">
                    Tidak ada guru yang cocok dengan pencarian atau filter Anda. Coba kata kunci lain atau reset filter.
                </x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada data guru" icon="teachers">
                    Tambahkan akun guru/tentor agar dapat ditugaskan ke kelas dan jadwal mengajar.
                </x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Guru</th>
                            <th scope="col">Email</th>
                            <th scope="col">No. Telepon</th>
                            <th scope="col">Bidang Keahlian</th>
                            <th scope="col">Bergabung</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teachers as $teacher)
                            <tr>
                                <td class="is-muted">{{ $teachers->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="admin-cell-name">{{ $teacher->name }}</span>
                                    @if ($teacher->teacherProfile?->last_education)
                                        <span class="admin-cell-sub">{{ $teacher->teacherProfile->last_education->label() }}</span>
                                    @endif
                                </td>
                                <td>{{ $teacher->email }}</td>
                                <td>{{ $teacher->phone ?? '—' }}</td>
                                <td>
                                    @if ($teacher->teacherProfile?->subject)
                                        <span class="admin-badge is-success">{{ $teacher->teacherProfile->subject }}</span>
                                    @else
                                        <span class="admin-badge is-neutral">Belum diisi</span>
                                    @endif
                                </td>
                                <td class="is-muted">{{ $teacher->created_at?->translatedFormat('d M Y') }}</td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('admin.teachers.show', $teacher) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Detail {{ $teacher->name }}">
                                            <x-admin.icon name="eye" :size="14" /> Detail
                                        </a>
                                        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Edit {{ $teacher->name }}">
                                            <x-admin.icon name="edit" :size="14" /> Edit
                                        </a>
                                        <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST"
                                              data-confirm="Hapus guru {{ $teacher->name }}? Akun tidak dapat digunakan untuk login lagi, tetapi datanya tetap tersimpan sebagai arsip.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn admin-btn-danger-outline admin-btn-sm" aria-label="Hapus {{ $teacher->name }}">
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

            @if ($teachers->hasPages())
                {{ $teachers->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $teachers->total() }} guru.</p>
            @endif
        @endif
    </section>
@endsection
