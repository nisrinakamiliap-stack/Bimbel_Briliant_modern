@extends('layouts.admin')

@section('title', 'Koreksi Absensi Guru')
@section('page_title', 'Absensi')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.attendances.teachers.index') }}">Absensi Guru</a></li>
                <li aria-current="page">Koreksi</li>
            </ol>
            <h2>Koreksi Absensi Guru</h2>
            <p>Hanya status yang dikoreksi; waktu check-in dan check-out dari server tetap tersimpan apa adanya.</p>
        </div>
    </div>

    <x-admin.flash />

    @include('admin.attendances.partials.correction-form', [
        'attendance' => $attendance,
        'statuses' => $statuses,
        'action' => route('admin.attendances.teachers.update', $attendance),
        'cancelUrl' => route('admin.attendances.teachers.index'),
        'details' => [
            'Guru' => $attendance->teacher->name,
            'Kelas' => $attendance->schedule->classroom->name,
            'Sesi' => $attendance->session_date->translatedFormat('l, d F Y').' · '.$attendance->schedule->timeRange(),
            'Check-in' => $attendance->checked_in_at->format('H:i:s'),
            'Check-out' => $attendance->checked_out_at?->format('H:i:s') ?? 'Belum check-out',
            'Durasi' => $attendance->durationLabel(),
        ],
    ])
@endsection
