@extends('layouts.teacher')

@section('title', 'Edit Materi')

@section('content')
    <nav aria-label="Breadcrumb">
        <ol class="admin-breadcrumb">
            <li><a href="{{ route('teacher.learning.materials.index') }}">Materi Pembelajaran</a></li>
            <li><a href="{{ route('teacher.learning.materials.show', $material) }}">{{ $material->title }}</a></li>
            <li aria-current="page">Edit</li>
        </ol>
    </nav>

    <div class="teacher-page-head">
        <div>
            <h1>Edit Materi</h1>
            <p>Untuk mengarsipkan, ubah status menjadi Diarsipkan. Materi tidak dihapus agar riwayat belajar murid tetap utuh.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('teacher.learning.materials.update', $material) }}" method="POST" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf
            @method('PUT')
            @include('admin.learning.materials._form', ['material' => $material, 'showTeacher' => false])
            <div class="admin-form-actions">
                <a href="{{ route('teacher.learning.materials.show', $material) }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary" data-loading-label="Menyimpan...">Simpan Perubahan</button>
            </div>
        </form>
    </section>
@endsection
