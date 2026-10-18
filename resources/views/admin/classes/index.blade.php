@extends('layouts.admin')

@section('title', 'Data Kelas')
@section('page_title', 'Data Kelas')

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Data Kelas</h2>
            <p>Kelola kelas belajar di setiap program: guru pengajar, kapasitas, mode belajar, dan status.</p>
        </div>
        <a href="{{ route('admin.classes.create') }}" class="admin-btn admin-btn-primary" id="btn-add-class">
            <x-admin.icon name="plus" :size="16" />
            Tambah Kelas
        </a>
    </div>

    <section class="admin-panel" aria-labelledby="classes-heading">
        <h3 id="classes-heading" class="sr-only">Daftar kelas</h3>

        <form action="{{ route('admin.classes.index') }}" method="GET" class="admin-toolbar" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama atau kode kelas</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $search }}" class="admin-input" placeholder="Cari nama atau kode kelas..." maxlength="100">
            </div>
            <div class="admin-toolbar-row">
                <label for="program" class="sr-only">Filter program bimbel</label>
                <select id="program" name="program" class="admin-select">
                    <option value="">Semua program</option>
                    @foreach ($programs as $programOption)
                        <option value="{{ $programOption->id }}" @selected($programId === $programOption->id)>{{ $programOption->name }}</option>
                    @endforeach
                </select>
                <label for="status" class="sr-only">Filter status</label>
                <select id="status" name="status" class="admin-select">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($status === 'nonaktif')>Tidak Aktif</option>
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($search !== '' || $programId || $status)
                    <a href="{{ route('admin.classes.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($classrooms->isEmpty())
            @if ($search !== '' || $programId || $status)
                <x-admin.empty-state title="Kelas tidak ditemukan" icon="search">
                    Tidak ada kelas yang cocok dengan pencarian atau filter Anda. Coba kata kunci lain atau reset filter.
                </x-admin.empty-state>
            @elseif ($programs->isEmpty())
                <x-admin.empty-state title="Belum ada program bimbel" icon="programs">
                    Kelas harus berada dalam sebuah program. Buat program terlebih dahulu di menu Program Bimbel.
                </x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada kelas" icon="classes">
                    Tambahkan kelas pertama, lalu tentukan guru pengajar dan kapasitasnya.
                </x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Kelas</th>
                            <th scope="col">Program Bimbel</th>
                            <th scope="col">Guru Pengajar</th>
                            <th scope="col">Kapasitas</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classrooms as $classroom)
                            <tr>
                                <td class="is-muted">{{ $classrooms->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="admin-cell-name">{{ $classroom->name }}</span>
                                    <span class="admin-cell-sub">{{ $classroom->code ?? 'Tanpa kode' }} · {{ $classroom->learning_mode->label() }}</span>
                                </td>
                                <td>{{ $classroom->program->name }}</td>
                                <td>
                                    @if ($classroom->teacher)
                                        {{ $classroom->teacher->name }}
                                        @if ($classroom->teacher->trashed())
                                            <span class="admin-cell-sub">Akun guru dihapus</span>
                                        @endif
                                    @else
                                        <span class="admin-badge is-pending">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $classroom->occupied_seats }}/{{ $classroom->capacity }} murid
                                    @if ($classroom->occupied_seats >= $classroom->capacity)
                                        <span class="admin-badge is-danger">Penuh</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($classroom->is_active)
                                        <span class="admin-badge is-success">Aktif</span>
                                    @else
                                        <span class="admin-badge is-neutral">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('admin.classes.show', $classroom) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Detail {{ $classroom->name }}">
                                            <x-admin.icon name="eye" :size="14" /> Detail
                                        </a>
                                        <a href="{{ route('admin.classes.edit', $classroom) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Edit {{ $classroom->name }}">
                                            <x-admin.icon name="edit" :size="14" /> Edit
                                        </a>
                                        @include('admin.classes._toggle-status', ['classroom' => $classroom, 'isSmall' => true])
                                        <form action="{{ route('admin.classes.destroy', $classroom) }}" method="POST"
                                              data-confirm="Hapus kelas {{ $classroom->name }} secara permanen? Jika kelas sudah memiliki riwayat, penghapusan akan ditolak dan Anda disarankan menonaktifkannya.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn admin-btn-danger-outline admin-btn-sm" aria-label="Hapus {{ $classroom->name }}">
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

            @if ($classrooms->hasPages())
                {{ $classrooms->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $classrooms->total() }} kelas.</p>
            @endif
        @endif
    </section>
@endsection
