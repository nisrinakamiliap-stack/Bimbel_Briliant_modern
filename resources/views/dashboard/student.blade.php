@extends('layouts.app')

@section('title', 'Dashboard Murid — Bimbel Modern Brilliant')

@section('content')
    <x-dashboard-placeholder
        title="Dashboard Murid"
        description="Pantau perjalanan belajarmu di sini."
        :features="['Program & pendaftaran', 'Status pembayaran', 'Jadwal kelas', 'Materi belajar', 'Tugas & ujian', 'Nilai & absensi', 'Notifikasi']" />
@endsection
