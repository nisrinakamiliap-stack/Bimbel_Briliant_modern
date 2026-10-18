@extends('layouts.admin')

@section('title', 'Tambah Pendaftaran')
@section('page_title', 'Pendaftaran Murid')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.enrollments.index') }}">Pendaftaran Murid</a></li>
                <li aria-current="page">Tambah Pendaftaran</li>
            </ol>
            <h2>Tambah Pendaftaran</h2>
            <p>Daftarkan murid yang sudah memiliki akun ke program bimbel. Status awal: Menunggu Verifikasi.</p>
        </div>
    </div>

    <x-admin.flash />

    @if ($students->isEmpty() || $programs->isEmpty())
        <div class="admin-alert is-error" role="alert" id="alert-missing-data">
            <x-admin.icon name="alert" :size="18" />
            <span>
                @if ($students->isEmpty())
                    Belum ada akun murid. <a href="{{ route('admin.students.create') }}" style="color: inherit; font-weight: 700;">Tambahkan murid terlebih dahulu</a>.
                @endif
                @if ($programs->isEmpty())
                    Belum ada program aktif. <a href="{{ route('admin.programs.create') }}" style="color: inherit; font-weight: 700;">Buat atau aktifkan program terlebih dahulu</a>.
                @endif
            </span>
        </div>
    @endif

    <section class="admin-panel">
        <form action="{{ route('admin.enrollments.store') }}" method="POST" class="admin-form" id="form-create-enrollment" novalidate>
            @csrf

            @include('admin.enrollments._form', ['enrollment' => null])

            <div class="admin-form-actions">
                <a href="{{ route('admin.enrollments.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary" @disabled($students->isEmpty() || $programs->isEmpty())>
                    <x-admin.icon name="check" :size="16" />
                    Simpan Pendaftaran
                </button>
            </div>
        </form>
    </section>
@endsection
