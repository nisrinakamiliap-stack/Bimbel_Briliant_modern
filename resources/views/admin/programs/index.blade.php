@extends('layouts.admin')

@section('title', 'Data Program Bimbel')
@section('page_title', 'Data Program Bimbel')

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Data Program Bimbel</h2>
            <p>Kelola program belajar yang ditawarkan Bimbel Sentra Edukasi: jenjang, harga, durasi, dan status.</p>
        </div>
        <a href="{{ route('admin.programs.create') }}" class="admin-btn admin-btn-primary" id="btn-add-program">
            <x-admin.icon name="plus" :size="16" />
            Tambah Program
        </a>
    </div>

    <section class="admin-panel" aria-labelledby="programs-heading">
        <h3 id="programs-heading" class="sr-only">Daftar program</h3>

        <form action="{{ route('admin.programs.index') }}" method="GET" class="admin-toolbar" role="search">
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama program</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $search }}" class="admin-input" placeholder="Cari nama program..." maxlength="100">
            </div>
            <div class="admin-toolbar-row">
                <label for="jenjang" class="sr-only">Filter jenjang</label>
                <select id="jenjang" name="jenjang" class="admin-select">
                    <option value="">Semua jenjang</option>
                    @foreach ($levels as $levelOption)
                        <option value="{{ $levelOption->value }}" @selected($level === $levelOption->value)>{{ $levelOption->label() }}</option>
                    @endforeach
                </select>
                <label for="status" class="sr-only">Filter status</label>
                <select id="status" name="status" class="admin-select">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($status === 'nonaktif')>Tidak Aktif</option>
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($search !== '' || $level || $status)
                    <a href="{{ route('admin.programs.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($programs->isEmpty())
            @if ($search !== '' || $level || $status)
                <x-admin.empty-state title="Program tidak ditemukan" icon="search">
                    Tidak ada program yang cocok dengan pencarian atau filter Anda. Coba kata kunci lain atau reset filter.
                </x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada program" icon="programs">
                    Tambahkan program bimbel pertama agar dapat ditawarkan kepada calon murid.
                </x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Program</th>
                            <th scope="col">Jenjang</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Durasi</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($programs as $program)
                            <tr>
                                <td class="is-muted">{{ $programs->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="admin-cell-name">{{ $program->name }}</span>
                                    <span class="admin-cell-sub">Dibuat {{ $program->created_at?->translatedFormat('d M Y') }}</span>
                                </td>
                                <td>{{ $program->level->label() }}</td>
                                <td>{{ $program->formattedPrice() }}</td>
                                <td class="is-muted">{{ $program->duration_months ? $program->duration_months.' bulan' : 'Fleksibel' }}</td>
                                <td>
                                    @if ($program->is_active)
                                        <span class="admin-badge is-success">Aktif</span>
                                    @else
                                        <span class="admin-badge is-neutral">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('admin.programs.show', $program) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Detail {{ $program->name }}">
                                            <x-admin.icon name="eye" :size="14" /> Detail
                                        </a>
                                        <a href="{{ route('admin.programs.edit', $program) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Edit {{ $program->name }}">
                                            <x-admin.icon name="edit" :size="14" /> Edit
                                        </a>
                                        @include('admin.programs._toggle-status', ['program' => $program, 'isSmall' => true])
                                        <form action="{{ route('admin.programs.destroy', $program) }}" method="POST"
                                              data-confirm="Hapus program {{ $program->name }} secara permanen? Jika program masih dipakai, penghapusan akan ditolak dan Anda disarankan menonaktifkannya.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn admin-btn-danger-outline admin-btn-sm" aria-label="Hapus {{ $program->name }}">
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

            @if ($programs->hasPages())
                {{ $programs->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $programs->total() }} program.</p>
            @endif
        @endif
    </section>
@endsection
