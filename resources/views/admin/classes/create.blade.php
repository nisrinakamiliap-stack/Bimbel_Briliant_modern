@extends('layouts.admin')

@section('title', 'Tambah Kelas')
@section('page_title', 'Data Kelas')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.classes.index') }}">Data Kelas</a></li>
                <li aria-current="page">Tambah Kelas</li>
            </ol>
            <h2>Tambah Kelas</h2>
            <p>Buat kelas baru dalam salah satu program bimbel.</p>
        </div>
    </div>

    <x-admin.flash />

    @if ($programs->isEmpty())
        <div class="admin-alert is-error" role="alert" id="alert-no-program">
            <x-admin.icon name="alert" :size="18" />
            <span>
                Belum ada program bimbel yang aktif. Kelas harus berada dalam sebuah program.
                <a href="{{ route('admin.programs.create') }}" style="color: inherit; font-weight: 700;">Buat program terlebih dahulu</a>.
            </span>
        </div>
    @endif

    <section class="admin-panel">
        <form action="{{ route('admin.classes.store') }}" method="POST" class="admin-form" id="form-create-class" novalidate>
            @csrf

            @include('admin.classes._form', ['classroom' => null])

            <div class="admin-form-actions">
                <a href="{{ route('admin.classes.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary" @disabled($programs->isEmpty())>
                    <x-admin.icon name="check" :size="16" />
                    Simpan Kelas
                </button>
            </div>
        </form>
    </section>
@endsection
