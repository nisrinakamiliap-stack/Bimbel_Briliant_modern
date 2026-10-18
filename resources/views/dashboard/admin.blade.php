@extends('layouts.app')

@section('title', 'Dashboard Admin — Bimbel Modern Brilliant')

@section('content')
    <x-dashboard-placeholder
        title="Dashboard Admin"
        description="Kelola seluruh operasional bimbel dari halaman ini."
        :features="['Manajemen murid', 'Manajemen guru', 'Manajemen program', 'Manajemen kelas', 'Manajemen jadwal', 'Manajemen pendaftaran', 'Verifikasi pembayaran', 'Laporan']" />
@endsection
