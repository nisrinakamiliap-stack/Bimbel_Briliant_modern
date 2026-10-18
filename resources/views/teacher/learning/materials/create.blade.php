@extends('layouts.teacher')

@section('title', 'Tambah Materi')

@section('content')
    <nav aria-label="Breadcrumb">
        <ol class="admin-breadcrumb">
            <li><a href="{{ route('teacher.learning.materials.index') }}">Materi Pembelajaran</a></li>
            <li aria-current="page">Tambah Materi</li>
        </ol>
    </nav>

    <div class="teacher-page-head">
        <div>
            <h1>Tambah Materi</h1>
            <p>Simpan sebagai Draf untuk disiapkan dulu, atau pilih Terbit agar langsung terlihat oleh murid kelas tujuan.</p>
        </div>
    </div>

    <x-admin.flash />

    @if ($classrooms->isEmpty())
        <div class="admin-alert is-error" role="alert">
            <x-admin.icon name="alert" :size="18" />
            <span>Anda belum mengampu kelas aktif, jadi materi belum dapat ditambahkan. Hubungi admin untuk penugasan kelas.</span>
        </div>
    @endif

    <section class="admin-panel">
        <form action="{{ route('teacher.learning.materials.store') }}" method="POST" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf
            @include('admin.learning.materials._form', ['material' => null, 'showTeacher' => false])
            <div class="admin-form-actions">
                <a href="{{ route('teacher.learning.materials.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary" data-loading-label="Menyimpan..." @disabled($classrooms->isEmpty())>Simpan Materi</button>
            </div>
        </form>
    </section>
@endsection
