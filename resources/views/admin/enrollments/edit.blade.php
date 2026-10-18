@extends('layouts.admin')

@section('title', 'Edit Pendaftaran')
@section('page_title', 'Pendaftaran Murid')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.enrollments.index') }}">Pendaftaran Murid</a></li>
                <li><a href="{{ route('admin.enrollments.show', $enrollment) }}">{{ $enrollment->student->name }} · {{ $enrollment->program->name }}</a></li>
                <li aria-current="page">Edit</li>
            </ol>
            <h2>Edit Pendaftaran</h2>
            <p>
                @if ($enrollment->status->isActive())
                    Perbarui penempatan kelas, tanggal, atau catatan pendaftaran.
                @else
                    Pendaftaran {{ strtolower($enrollment->status->label()) }}: hanya catatan admin yang dapat diubah.
                @endif
            </p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.enrollments.update', $enrollment) }}" method="POST" class="admin-form" id="form-edit-enrollment" novalidate>
            @csrf
            @method('PUT')

            @include('admin.enrollments._form', ['enrollment' => $enrollment])

            <div class="admin-form-actions">
                <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-admin.icon name="check" :size="16" />
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </section>
@endsection
