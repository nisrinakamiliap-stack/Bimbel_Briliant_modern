@extends('layouts.admin')

@section('title', $program->name.' — Data Program Bimbel')
@section('page_title', 'Data Program Bimbel')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.programs.index') }}">Data Program Bimbel</a></li>
                <li aria-current="page">{{ $program->name }}</li>
            </ol>
            <h2>Detail Program</h2>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.programs.index') }}" class="admin-btn admin-btn-secondary">
                <x-admin.icon name="arrow-left" :size="16" />
                Kembali
            </a>
            <a href="{{ route('admin.programs.edit', $program) }}" class="admin-btn admin-btn-primary">
                <x-admin.icon name="edit" :size="16" />
                Edit
            </a>
        </div>
    </div>

    <x-admin.flash />

    <div class="admin-grid">
        <section class="admin-panel is-wide" aria-labelledby="program-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="program-heading">{{ $program->name }}</h3>
                    <p>{{ $program->level->label() }}</p>
                </div>
                @if ($program->is_active)
                    <span class="admin-badge is-success">Aktif</span>
                @else
                    <span class="admin-badge is-neutral">Tidak Aktif</span>
                @endif
            </div>
            <dl class="admin-detail-list">
                <div>
                    <dt>Harga</dt>
                    <dd>{{ $program->formattedPrice() }}</dd>
                </div>
                <div>
                    <dt>Durasi</dt>
                    <dd>{{ $program->duration_months ? $program->duration_months.' bulan' : 'Fleksibel (per bulan)' }}</dd>
                </div>
                <div>
                    <dt>Jenjang</dt>
                    <dd>{{ $program->level->label() }}</dd>
                </div>
                <div>
                    <dt>Slug / Kode URL</dt>
                    <dd>{{ $program->slug }}</dd>
                </div>
                <div>
                    <dt>Tanggal Dibuat</dt>
                    <dd>{{ $program->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Terakhir Diperbarui</dt>
                    <dd>{{ $program->updated_at?->translatedFormat('d F Y, H:i') ?? '—' }}</dd>
                </div>
                <div style="grid-column: 1 / -1;">
                    <dt>Deskripsi</dt>
                    <dd>{{ $program->description ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="admin-panel" aria-labelledby="status-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="status-heading">Status Program</h3>
                    <p>{{ $program->is_active ? 'Program sedang ditawarkan.' : 'Program tidak ditawarkan, data tetap tersimpan.' }}</p>
                </div>
                @include('admin.programs._toggle-status', ['program' => $program, 'isSmall' => false])
            </div>
        </section>

        <section class="admin-panel" aria-labelledby="danger-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="danger-heading">Hapus Program</h3>
                    @if ($isDeletable)
                        <p>Program belum digunakan oleh kelas, pendaftaran, atau pembayaran sehingga aman dihapus.</p>
                    @else
                        <p>Program sudah digunakan oleh data lain dan tidak dapat dihapus. Nonaktifkan program ini.</p>
                    @endif
                </div>
                @if ($isDeletable)
                    <form action="{{ route('admin.programs.destroy', $program) }}" method="POST"
                          data-confirm="Hapus program {{ $program->name }} secara permanen? Tindakan ini tidak dapat dibatalkan.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-danger-outline" id="btn-delete-program">
                            <x-admin.icon name="trash" :size="16" />
                            Hapus
                        </button>
                    </form>
                @endif
            </div>
        </section>
    </div>
@endsection
