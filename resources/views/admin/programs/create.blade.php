@extends('layouts.admin')

@section('title', 'Tambah Program')
@section('page_title', 'Data Program Bimbel')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.programs.index') }}">Data Program Bimbel</a></li>
                <li aria-current="page">Tambah Program</li>
            </ol>
            <h2>Tambah Program</h2>
            <p>Lengkapi informasi program bimbel yang akan ditawarkan.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.programs.store') }}" method="POST" class="admin-form" id="form-create-program" novalidate>
            @csrf

            @include('admin.programs._form', ['program' => null])

            <div class="admin-form-actions">
                <a href="{{ route('admin.programs.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-admin.icon name="check" :size="16" />
                    Simpan Program
                </button>
            </div>
        </form>
    </section>
@endsection
