@extends('layouts.teacher')

@section('title', 'Edit Tugas')

@section('content')
    <nav aria-label="Breadcrumb">
        <ol class="admin-breadcrumb">
            <li><a href="{{ route('teacher.learning.assignments.index') }}">Tugas</a></li>
            <li><a href="{{ route('teacher.learning.assignments.show', $assignment) }}">{{ $assignment->title }}</a></li>
            <li aria-current="page">Edit</li>
        </ol>
    </nav>

    <div class="teacher-page-head">
        <div>
            <h1>Edit Tugas</h1>
            <p>Untuk mengarsipkan, ubah status menjadi Diarsipkan. Tugas yang sudah memiliki pengumpulan tidak dapat dihapus.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('teacher.learning.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf
            @method('PUT')
            @include('admin.learning.assignments._form', ['assignment' => $assignment, 'showTeacher' => false])
            <div class="admin-form-actions">
                <a href="{{ route('teacher.learning.assignments.show', $assignment) }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary" data-loading-label="Menyimpan...">Simpan Perubahan</button>
            </div>
        </form>
    </section>
@endsection
