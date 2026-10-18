@extends('layouts.app')

@section('title', 'Dashboard Murid — Bimbel Sentra Edukasi')

@section('content')
    <x-dashboard-placeholder
        title="Dashboard Murid"
        description="Pantau perjalanan belajarmu di sini."
        :features="['Program & pendaftaran', 'Status pembayaran', 'Jadwal kelas', 'Nilai & absensi', 'Notifikasi']" />

    <div class="container-main" style="padding-bottom: 3rem;">
        <a href="{{ route('student.learning.index') }}" class="auth-btn auth-btn--auto" id="link-student-learning" style="padding: .75rem 1.5rem;">
            Buka Pembelajaran Saya (Materi &amp; Tugas)
        </a>
    </div>
@endsection
