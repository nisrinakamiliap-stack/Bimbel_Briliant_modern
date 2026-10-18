@extends('layouts.admin')

@section('title', 'Edit Tugas')
@section('page_title', 'Pembelajaran')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.learning.assignments.index') }}">Tugas</a></li>
                <li><a href="{{ route('admin.learning.assignments.show', $assignment) }}">{{ $assignment->title }}</a></li>
                <li aria-current="page">Edit</li>
            </ol>
            <h2>Edit Tugas</h2>
            <p>Untuk mengarsipkan, ubah status menjadi Diarsipkan.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.learning.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf
            @method('PUT')
            @include('admin.learning.assignments._form', ['assignment' => $assignment])
            <div class="admin-form-actions">
                <a href="{{ route('admin.learning.assignments.show', $assignment) }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary"><x-admin.icon name="check" :size="16" /> Simpan Perubahan</button>
            </div>
        </form>
    </section>
@endsection
