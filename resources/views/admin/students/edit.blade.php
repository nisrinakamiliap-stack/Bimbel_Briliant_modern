@extends('layouts.admin')

@section('title', 'Edit Murid')
@section('page_title', 'Data Murid')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.students.index') }}">Data Murid</a></li>
                <li><a href="{{ route('admin.students.show', $student) }}">{{ $student->name }}</a></li>
                <li aria-current="page">Edit</li>
            </ol>
            <h2>Edit Murid</h2>
            <p>Perbarui data akun dan profil murid. Password tidak diubah melalui form ini.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.students.update', $student) }}" method="POST" class="admin-form" id="form-edit-student" novalidate>
            @csrf
            @method('PUT')

            @include('admin.students._form', ['student' => $student])

            <div class="admin-form-actions">
                <a href="{{ route('admin.students.show', $student) }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-admin.icon name="check" :size="16" />
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </section>
@endsection
