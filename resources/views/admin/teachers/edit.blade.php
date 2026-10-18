@extends('layouts.admin')

@section('title', 'Edit Guru')
@section('page_title', 'Data Guru')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.teachers.index') }}">Data Guru</a></li>
                <li><a href="{{ route('admin.teachers.show', $teacher) }}">{{ $teacher->name }}</a></li>
                <li aria-current="page">Edit</li>
            </ol>
            <h2>Edit Guru</h2>
            <p>Perbarui data akun dan profil pengajar. Password tidak diubah melalui form ini.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.teachers.update', $teacher) }}" method="POST" class="admin-form" id="form-edit-teacher" novalidate>
            @csrf
            @method('PUT')

            @include('admin.teachers._form', ['teacher' => $teacher])

            <div class="admin-form-actions">
                <a href="{{ route('admin.teachers.show', $teacher) }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-admin.icon name="check" :size="16" />
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </section>
@endsection
