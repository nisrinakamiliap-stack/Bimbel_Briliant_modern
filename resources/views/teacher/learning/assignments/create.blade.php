@extends('layouts.teacher')

@section('title', 'Buat Tugas')

@section('content')
    <nav aria-label="Breadcrumb">
        <ol class="admin-breadcrumb">
            <li><a href="{{ route('teacher.learning.assignments.index') }}">Tugas</a></li>
            <li aria-current="page">Buat Tugas</li>
        </ol>
    </nav>

    <div class="teacher-page-head">
        <div>
            <h1>Buat Tugas</h1>
            <p>Murid kelas tujuan menerima notifikasi saat tugas berstatus Terbit. Tenggat dihitung dengan zona waktu aplikasi.</p>
        </div>
    </div>

    <x-admin.flash />

    @if ($classrooms->isEmpty())
        <div class="admin-alert is-error" role="alert">
            <x-admin.icon name="alert" :size="18" />
            <span>Anda belum mengampu kelas aktif, jadi tugas belum dapat dibuat. Hubungi admin untuk penugasan kelas.</span>
        </div>
    @endif

    <section class="admin-panel">
        <form action="{{ route('teacher.learning.assignments.store') }}" method="POST" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf
            @include('admin.learning.assignments._form', ['assignment' => null, 'showTeacher' => false])
            <div class="admin-form-actions">
                <a href="{{ route('teacher.learning.assignments.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary" data-loading-label="Menyimpan..." @disabled($classrooms->isEmpty())>Simpan Tugas</button>
            </div>
        </form>
    </section>
@endsection
