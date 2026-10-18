@extends('layouts.admin')

@section('title', 'Koreksi Absensi Murid')
@section('page_title', 'Absensi')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.attendances.students.index') }}">Absensi Murid</a></li>
                <li aria-current="page">Koreksi</li>
            </ol>
            <h2>Koreksi Absensi Murid</h2>
            <p>Status sebelumnya, admin yang mengoreksi, dan alasan koreksi akan tersimpan.</p>
        </div>
    </div>

    <x-admin.flash />

    @include('admin.attendances.partials.correction-form', [
        'attendance' => $attendance,
        'statuses' => $statuses,
        'action' => route('admin.attendances.students.update', $attendance),
        'cancelUrl' => route('admin.attendances.students.index'),
        'details' => [
            'Murid' => $attendance->student->name,
            'Kelas' => $attendance->schedule->classroom->name,
            'Guru' => $attendance->schedule->classroom->teacher?->name ?? 'Belum ditentukan',
            'Sesi' => $attendance->session_date->translatedFormat('l, d F Y').' · '.$attendance->schedule->timeRange(),
            'Dicatat Oleh' => ($attendance->recorder?->name ?? '—').' · '.$attendance->created_at?->translatedFormat('d M Y, H:i'),
            'Keterangan' => $attendance->notes ?? '—',
        ],
    ])
@endsection
