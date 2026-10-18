@extends('layouts.admin')

@section('title', 'Tambah Murid')
@section('page_title', 'Data Murid')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.students.index') }}">Data Murid</a></li>
                <li aria-current="page">Tambah Murid</li>
            </ol>
            <h2>Tambah Murid</h2>
            <p>Buat akun murid baru beserta profilnya.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.students.store') }}" method="POST" class="admin-form" id="form-create-student" novalidate>
            @csrf

            @include('admin.students._form', ['student' => null])

            <div class="admin-form-actions">
                <a href="{{ route('admin.students.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-admin.icon name="check" :size="16" />
                    Simpan Murid
                </button>
            </div>
        </form>
    </section>
@endsection
