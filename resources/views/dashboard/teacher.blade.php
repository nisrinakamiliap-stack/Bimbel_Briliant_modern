@extends('layouts.app')

@section('title', 'Dashboard Guru — Bimbel Sentra Edukasi')

@section('content')
    <x-dashboard-placeholder
        title="Dashboard Guru/Tentor"
        description="Kelola kegiatan pembelajaran pada kelas yang Anda ampu."
        :features="['Kelas yang ditugaskan', 'Jadwal mengajar', 'Materi pembelajaran', 'Tugas & penilaian', 'Ujian & soal', 'Absensi', 'Pengumuman']" />
@endsection
