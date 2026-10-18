@extends('layouts.admin')

@section('title', 'Edit Kelas')
@section('page_title', 'Data Kelas')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.classes.index') }}">Data Kelas</a></li>
                <li><a href="{{ route('admin.classes.show', $classroom) }}">{{ $classroom->name }}</a></li>
                <li aria-current="page">Edit</li>
            </ol>
            <h2>Edit Kelas</h2>
            <p>Perbarui program, guru pengajar, kapasitas, atau status kelas.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.classes.update', $classroom) }}" method="POST" class="admin-form" id="form-edit-class" novalidate>
            @csrf
            @method('PUT')

            @include('admin.classes._form', ['classroom' => $classroom])

            <div class="admin-form-actions">
                <a href="{{ route('admin.classes.show', $classroom) }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-admin.icon name="check" :size="16" />
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </section>
@endsection
