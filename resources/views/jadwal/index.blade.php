@extends('layouts.app')

@section('title', 'Jadwal Pembelajaran — Bimbel Modern Brilliant')
@section('meta_description', 'Lihat jadwal lengkap sesi bimbingan belajar untuk siswa SD, SMP, SMA, dan persiapan ujian di Bimbel Modern Brilliant. Pilih waktu belajar yang paling sesuai.')

@section('content')
{{-- HEADER / BREADCRUMB SECTION --}}
<section style="background-color: #ffffff; padding-top: 2.5rem; padding-bottom: 2.75rem; border-bottom: 1px solid #f1f5f9;">
    <div class="container-main">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" style="margin-bottom: 1.5rem;">
            <ol style="display: flex; align-items: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.875rem;">
                <li>
                    <a href="{{ route('home') }}" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#16845B'" onmouseout="this.style.color='#64748b'">Beranda</a>
                </li>
                <li style="color: #cbd5e1;" aria-hidden="true">/</li>
                <li style="color: #142B4A; font-weight: 600;" aria-current="page">Jadwal Pembelajaran</li>
            </ol>
        </nav>

        {{-- Header Title & Subtitle --}}
        <div style="text-align: center; max-width: 680px; margin: 0 auto;">
            <div class="divider-green" style="margin-left: auto; margin-right: auto;"></div>
            <p class="text-section-label" style="margin-bottom: 0.75rem;">Agenda Kelas</p>
            <h1 class="heading-xl" style="margin-bottom: 1rem; color: #142B4A;">
                Jadwal Pembelajaran
            </h1>
            <p style="font-size: 1.0625rem; color: #4a5568; line-height: 1.75; margin: 0 auto;">
                Lihat semua kelas yang tersedia dan pilih waktu yang sesuai dengan kebutuhan belajarmu.
            </p>
        </div>
    </div>
</section>

{{-- KONTEN UTAMA: FILTER & DAFTAR JADWAL --}}
<section style="padding-top: 2.5rem; padding-bottom: 5rem; background-color: #F8FAFC;">
    <div class="container-main" style="max-width: 1040px;">

        {{-- BARIS KONTROL: FILTER TAB & NAVIGASI MINGGU --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2rem;">

            {{-- 1. Filter Tab (Pills) --}}
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div role="tablist" aria-label="Filter jenjang" style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    @php
                    $tabs = [
                        'semua' => 'Semua',
                        'sd'    => 'SD',
                        'smp'   => 'SMP',
                        'sma'   => 'SMA',
                        'utbk'  => 'UTBK',
                    ];
                    @endphp

                    @foreach ($tabs as $key => $label)
                        @php
                        $isActive = ($jenjangAktif === $key);
                        @endphp
                        <a href="{{ route('jadwal.index', ['jenjang' => $key, 'week' => $weekOffset]) }}"
                           class="filter-tab-pill"
                           id="tab-{{ $key }}"
                           role="tab"
                           aria-selected="{{ $isActive ? 'true' : 'false' }}"
                           style="display: inline-flex; align-items: center; padding: 0.5rem 1.125rem; border-radius: 999px; font-size: 0.875rem; text-decoration: none; transition: all 0.2s ease; cursor: pointer; {{ $isActive ? 'background-color:#16845B;color:#ffffff;font-weight:700;box-shadow:0 4px 12px rgba(22,132,91,0.25);border:1px solid #16845B;' : 'background-color:#ffffff;color:#4a5568;font-weight:600;border:1px solid #e2e8f0;' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                {{-- Indikator Jumlah Kelas --}}
                <div style="font-size: 0.875rem; color: #64748b; font-weight: 500;">
                    Menampilkan <strong style="color: #142B4A;">{{ $totalJadwal }}</strong> kelas
                </div>
            </div>

            {{-- 2. Navigasi Minggu --}}
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.875rem; padding: 0.875rem 1.25rem; box-shadow: 0 2px 8px rgba(20,43,74,0.02);">
                {{-- Tombol Minggu Sebelumnya --}}
                <a href="{{ route('jadwal.index', ['jenjang' => $jenjangAktif, 'week' => $prevWeek]) }}"
                   class="btn-week-nav"
                   id="btn-prev-week"
                   aria-label="Minggu Sebelumnya"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; color: #142B4A; text-decoration: none; font-size: 0.875rem; font-weight: 600; padding: 0.4rem 0.875rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background-color: #f8fafc; transition: all 0.2s ease;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Minggu Lalu</span>
                </a>

                {{-- Rentang Tanggal Tengah --}}
                <div style="display: flex; align-items: center; gap: 0.625rem; text-align: center;">
                    <div style="width: 2rem; height: 2rem; border-radius: 50%; background-color: #EAF6EF; display: flex; align-items: center; justify-content: center; color: #16845B; flex-shrink: 0;" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div>
                        <span id="label-rentang-tanggal" style="font-size: 1rem; font-weight: 700; color: #142B4A; display: block;">
                            {{ $rentangTanggal }}
                        </span>
                        @if ($weekOffset === 0)
                            <span style="font-size: 0.75rem; font-weight: 600; color: #16845B;">(Minggu Ini)</span>
                        @else
                            <a href="{{ route('jadwal.index', ['jenjang' => $jenjangAktif, 'week' => 0]) }}" style="font-size: 0.75rem; font-weight: 600; color: #16845B; text-decoration: underline;">
                                Kembali ke Minggu Ini
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Tombol Minggu Berikutnya --}}
                <a href="{{ route('jadwal.index', ['jenjang' => $jenjangAktif, 'week' => $nextWeek]) }}"
                   class="btn-week-nav"
                   id="btn-next-week"
                   aria-label="Minggu Berikutnya"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; color: #142B4A; text-decoration: none; font-size: 0.875rem; font-weight: 600; padding: 0.4rem 0.875rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background-color: #f8fafc; transition: all 0.2s ease;">
                    <span>Minggu Depan</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>

        </div>

        {{-- 3. DAFTAR KARTU JADWAL PER HARI --}}
        @if ($totalJadwal > 0)
            <div id="jadwal-container" role="feed" aria-label="Daftar jadwal kelas">
                @foreach ($jadwalList as $jadwal)
                    <x-jadwal-item :jadwal="$jadwal" />
                @endforeach
            </div>
        @else
            {{-- 4. PESAN KOSONG RAMAH (EMPTY STATE) --}}
            <div id="empty-state-jadwal" style="background-color: #ffffff; border: 1px dashed #cbd5e1; border-radius: 1rem; padding: 4rem 2rem; text-align: center; margin: 2rem 0;">
                <div style="width: 4.5rem; height: 4.5rem; background-color: #F8FAFC; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #94a3b8;" aria-hidden="true">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <line x1="10" y1="14" x2="14" y2="18"></line>
                        <line x1="14" y1="14" x2="10" y2="18"></line>
                    </svg>
                </div>
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #142B4A; margin-bottom: 0.5rem;">
                    Tidak Ada Jadwal Kelas Ditemukan
                </h2>
                <p style="font-size: 0.9375rem; color: #64748b; line-height: 1.6; max-width: 480px; margin: 0 auto 1.75rem;">
                    @if ($jenjangAktif !== 'semua')
                        Belum ada kelas terjadwal untuk filter <strong>{{ strtoupper($jenjangAktif) }}</strong> pada rentang tanggal ini.
                    @else
                        Belum ada agenda kelas aktif pada minggu yang dipilih.
                    @endif
                    Silakan lihat jenjang lain atau kembali ke jadwal minggu ini.
                </p>
                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.75rem;">
                    <a href="{{ route('jadwal.index', ['jenjang' => 'semua', 'week' => 0]) }}" class="btn-primary btn-sm">
                        Lihat Semua Jadwal Minggu Ini
                    </a>
                    <a href="{{ url('/#contact') }}" class="btn-secondary btn-sm">
                        Request Jadwal Khusus
                    </a>
                </div>
            </div>
        @endif

        {{-- 5. BANNER KONSULTASI JADWAL KHUSUS / PRIVAT --}}
        <div style="margin-top: 3.5rem; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem 2.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem; box-shadow: 0 4px 16px rgba(20,43,74,0.04);">
            <div style="max-width: 620px;">
                <div style="display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #16845B; margin-bottom: 0.375rem;">
                    Jadwal Fleksibel
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #142B4A; margin-bottom: 0.375rem;">
                    Butuh Jadwal Privat atau Waktu Khusus?
                </h3>
                <p style="font-size: 0.875rem; color: #64748b; line-height: 1.6; margin: 0;">
                    Kami menyediakan opsi pendampingan privat 1-on-1 dengan jadwal yang bisa disesuaikan sepenuhnya dengan rutinitas sekolah Anda.
                </p>
            </div>
            <a href="{{ url('/#contact') }}" class="btn-primary" style="border-radius: 0.5rem; white-space: nowrap;">
                Konsultasi Jadwal
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
        </div>

    </div>
</section>
@endsection
