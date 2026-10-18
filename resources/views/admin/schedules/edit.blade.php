@extends('layouts.admin')

@section('title', 'Edit Jadwal')
@section('page_title', 'Jadwal Belajar')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.schedules.index') }}">Jadwal Belajar</a></li>
                <li><a href="{{ route('admin.schedules.show', $schedule) }}">{{ $schedule->classroom->name }} · {{ $schedule->summary() }}</a></li>
                <li aria-current="page">Edit</li>
            </ol>
            <h2>Edit Jadwal</h2>
            <p>Perbarui kelas, hari, jam, masa berlaku, ruangan, atau status jadwal.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST" class="admin-form" id="form-edit-schedule" novalidate>
            @csrf
            @method('PUT')

            @include('admin.schedules._form', ['schedule' => $schedule])

            <div class="admin-form-actions">
                <a href="{{ route('admin.schedules.show', $schedule) }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-admin.icon name="check" :size="16" />
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </section>
@endsection
