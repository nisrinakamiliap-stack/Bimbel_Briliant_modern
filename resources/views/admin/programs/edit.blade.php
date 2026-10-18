@extends('layouts.admin')

@section('title', 'Edit Program')
@section('page_title', 'Data Program Bimbel')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.programs.index') }}">Data Program Bimbel</a></li>
                <li><a href="{{ route('admin.programs.show', $program) }}">{{ $program->name }}</a></li>
                <li aria-current="page">Edit</li>
            </ol>
            <h2>Edit Program</h2>
            <p>Perbarui informasi, harga, durasi, atau status program.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.programs.update', $program) }}" method="POST" class="admin-form" id="form-edit-program" novalidate>
            @csrf
            @method('PUT')

            @include('admin.programs._form', ['program' => $program])

            <div class="admin-form-actions">
                <a href="{{ route('admin.programs.show', $program) }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-admin.icon name="check" :size="16" />
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </section>
@endsection
