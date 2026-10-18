@extends('layouts.admin')

@section('title', 'Edit Materi')
@section('page_title', 'Pembelajaran')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.learning.materials.index') }}">Materi Pembelajaran</a></li>
                <li><a href="{{ route('admin.learning.materials.show', $material) }}">{{ $material->title }}</a></li>
                <li aria-current="page">Edit</li>
            </ol>
            <h2>Edit Materi</h2>
            <p>Untuk mengarsipkan, ubah status menjadi Diarsipkan. Materi tidak dihapus agar riwayat tetap utuh.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.learning.materials.update', $material) }}" method="POST" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf
            @method('PUT')
            @include('admin.learning.materials._form', ['material' => $material])
            <div class="admin-form-actions">
                <a href="{{ route('admin.learning.materials.show', $material) }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary"><x-admin.icon name="check" :size="16" /> Simpan Perubahan</button>
            </div>
        </form>
    </section>
@endsection
