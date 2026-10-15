@extends('layouts.app')

@section('title', 'Program Belajar — Bimbel Modern Brilliant')
@section('meta_description', 'Pilih program bimbingan belajar terbaik sesuai kebutuhan siswa SD, SMP, SMA, dan persiapan ujian masuk sekolah favorit atau SNBT di Bimbel Modern Brilliant.')

@section('content')
{{-- HERO / HEADER SECTION --}}
<section style="background-color: #ffffff; padding-top: 2.5rem; padding-bottom: 3.5rem; border-bottom: 1px solid #f1f5f9;">
    <div class="container-main">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" style="margin-bottom: 1.75rem;">
            <ol style="display: flex; align-items: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.875rem;">
                <li>
                    <a href="{{ route('home') }}" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#16845B'" onmouseout="this.style.color='#64748b'">Beranda</a>
                </li>
                <li style="color: #cbd5e1;" aria-hidden="true">/</li>
                <li style="color: #142B4A; font-weight: 600;" aria-current="page">Program Belajar</li>
            </ol>
        </nav>

        {{-- Header Title & Subtitle --}}
        <div style="text-align: center; max-width: 680px; margin: 0 auto;">
            <div class="divider-green" style="margin-left: auto; margin-right: auto;"></div>
            <p class="text-section-label" style="margin-bottom: 0.75rem;">Program Kami</p>
            <h1 class="heading-xl" style="margin-bottom: 1rem; color: #142B4A;">
                Program Belajar
            </h1>
            <p style="font-size: 1.0625rem; color: #4a5568; line-height: 1.75; margin: 0 auto;">
                Pilih program sesuai kebutuhan dan target belajarmu. Setiap program dirancang untuk membantu siswa belajar lebih terarah.
            </p>
        </div>
    </div>
</section>

{{-- PROGRAM CARDS GRID --}}
<section style="padding-top: 3.5rem; padding-bottom: 5rem; background-color: #F8FAFC;">
    <div class="container-main">
        <div class="grid-programs" id="program-grid-list">
            @foreach ($programs as $program)
                <x-program-card :program="$program" />
            @endforeach
        </div>

        {{-- Help / Consultation Card --}}
        <div style="margin-top: 4rem; background: linear-gradient(135deg, #142B4A 0%, #1d3d6b 100%); border-radius: 1.25rem; padding: 2.5rem; color: #ffffff; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 2rem; box-shadow: 0 16px 40px rgba(20, 43, 74, 0.12);">
            <div style="max-width: 600px;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background-color: rgba(22, 132, 91, 0.25); color: #86efac; font-size: 0.8125rem; font-weight: 600; padding: 0.25rem 0.75rem; border-radius: 999px; margin-bottom: 0.75rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    Konsultasi Gratis
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 700; color: #ffffff; margin-bottom: 0.5rem;">
                    Masih Ragu Memilih Program yang Tepat?
                </h2>
                <p style="font-size: 0.9375rem; color: rgba(255, 255, 255, 0.8); line-height: 1.6; margin: 0;">
                    Diskusikan kebutuhan, jenjang kelas, target akademik, dan jadwal putra-putri Anda bersama konselor pendidikan kami tanpa dipungut biaya.
                </p>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <a href="{{ url('/#contact') }}" class="btn-primary" style="background-color: #16845B; border-color: #16845B;">
                    Konsultasi Sekarang
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
