@extends('layouts.admin')

@section('title', 'Tambah Jadwal')
@section('page_title', 'Jadwal Belajar')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.schedules.index') }}">Jadwal Belajar</a></li>
                <li aria-current="page">Tambah Jadwal</li>
            </ol>
            <h2>Tambah Jadwal</h2>
            <p>Tentukan hari dan jam belajar mingguan untuk sebuah kelas.</p>
        </div>
    </div>

    <x-admin.flash />

    @if ($classrooms->isEmpty())
        <div class="admin-alert is-error" role="alert" id="alert-no-classroom">
            <x-admin.icon name="alert" :size="18" />
            <span>
                Belum ada kelas aktif yang dapat dijadwalkan.
                <a href="{{ route('admin.classes.create') }}" style="color: inherit; font-weight: 700;">Tambahkan kelas terlebih dahulu</a>,
                lalu tetapkan guru pengajarnya.
            </span>
        </div>
    @endif

    <section class="admin-panel">
        <form action="{{ route('admin.schedules.store') }}" method="POST" class="admin-form" id="form-create-schedule" novalidate>
            @csrf

            @include('admin.schedules._form', ['schedule' => null])

            <div class="admin-form-actions">
                <a href="{{ route('admin.schedules.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary" @disabled($classrooms->isEmpty())>
                    <x-admin.icon name="check" :size="16" />
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </section>
@endsection
