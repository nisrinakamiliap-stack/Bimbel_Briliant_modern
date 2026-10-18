@extends('layouts.admin')

@section('title', 'Tambah Guru')
@section('page_title', 'Data Guru')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.teachers.index') }}">Data Guru</a></li>
                <li aria-current="page">Tambah Guru</li>
            </ol>
            <h2>Tambah Guru</h2>
            <p>Buat akun guru/tentor baru beserta profil pengajarnya.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.teachers.store') }}" method="POST" class="admin-form" id="form-create-teacher" novalidate>
            @csrf

            @include('admin.teachers._form', ['teacher' => null])

            <div class="admin-form-actions">
                <a href="{{ route('admin.teachers.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-admin.icon name="check" :size="16" />
                    Simpan Guru
                </button>
            </div>
        </form>
    </section>
@endsection
