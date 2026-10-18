@extends('layouts.admin')

@section('title', 'Tambah Tugas')
@section('page_title', 'Pembelajaran')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.learning.assignments.index') }}">Tugas</a></li>
                <li aria-current="page">Tambah Tugas</li>
            </ol>
            <h2>Tambah Tugas</h2>
        </div>
    </div>

    <x-admin.flash />

    @if ($classrooms->isEmpty())
        <div class="admin-alert is-error" role="alert">
            <x-admin.icon name="alert" :size="18" />
            <span>Belum ada kelas aktif. <a href="{{ route('admin.classes.create') }}" style="color: inherit; font-weight: 700;">Tambahkan kelas terlebih dahulu</a>.</span>
        </div>
    @endif

    <section class="admin-panel">
        <form action="{{ route('admin.learning.assignments.store') }}" method="POST" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf
            @include('admin.learning.assignments._form', ['assignment' => null])
            <div class="admin-form-actions">
                <a href="{{ route('admin.learning.assignments.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary" @disabled($classrooms->isEmpty())><x-admin.icon name="check" :size="16" /> Simpan Tugas</button>
            </div>
        </form>
    </section>
@endsection
