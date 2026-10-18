@extends('layouts.app')

@section('title', 'Daftar Akun Murid — Bimbel Sentra Edukasi')
@section('meta_description', 'Daftarkan akun murid di Bimbel Sentra Edukasi untuk memilih program, melihat jadwal, dan mulai belajar bersama tutor profesional.')

@push('styles')
    @include('auth.partials.styles')
@endpush

@section('content')
<section class="auth-section">
    <div class="container-main">
        <div class="auth-grid">

            {{-- Panel Identitas Brand (desktop) --}}
            <aside class="auth-brand-panel" aria-hidden="true">
                <span class="auth-badge">Pendaftaran Murid Baru</span>
                <h2>Mulai belajar lebih terarah bersama kami</h2>
                <p>Buat akun murid untuk memilih program belajar, memantau jadwal, dan mendapatkan pendampingan dari tutor berpengalaman.</p>
                <ul class="auth-brand-list">
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Program SD, SMP, SMA, dan persiapan ujian
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Materi terstruktur dan evaluasi berkala
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Data pribadi Anda tersimpan dengan aman
                    </li>
                </ul>
            </aside>

            {{-- Kartu Form Registrasi --}}
            <div class="auth-card">
                <h1>Daftar Akun Murid</h1>
                <p class="auth-subtitle">Lengkapi data di bawah ini untuk membuat akun baru.</p>

                <form action="{{ route('register.store') }}" method="POST" class="auth-form" id="form-register" novalidate>
                    @csrf

                    {{-- Nama Lengkap --}}
                    <div>
                        <label for="name" class="auth-label">Nama Lengkap <span class="req" aria-hidden="true">*</span></label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name') }}"
                               class="auth-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               placeholder="Contoh: Budi Santoso"
                               autocomplete="name"
                               maxlength="100"
                               required
                               autofocus
                               @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                        @error('name')
                            <p id="name-error" class="auth-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email & Telepon --}}
                    <div class="auth-two-col">
                        <div>
                            <label for="email" class="auth-label">Alamat Email <span class="req" aria-hidden="true">*</span></label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email') }}"
                                   class="auth-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                   placeholder="nama@email.com"
                                   autocomplete="email"
                                   required
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                            @error('email')
                                <p id="email-error" class="auth-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="auth-label">No. Telepon / WhatsApp <span class="req" aria-hidden="true">*</span></label>
                            <input type="tel"
                                   name="phone"
                                   id="phone"
                                   value="{{ old('phone') }}"
                                   class="auth-input {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                   placeholder="081234567890"
                                   autocomplete="tel"
                                   inputmode="tel"
                                   required
                                   @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                            @error('phone')
                                <p id="phone-error" class="auth-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Password --}}
                    <x-password-input name="password"
                                      label="Password"
                                      autocomplete="new-password"
                                      placeholder="Minimal 8 karakter"
                                      hint="Minimal 8 karakter dan mengandung huruf serta angka." />

                    {{-- Konfirmasi Password --}}
                    <x-password-input name="password_confirmation"
                                      label="Konfirmasi Password"
                                      autocomplete="new-password"
                                      placeholder="Ulangi password" />

                    <button type="submit" class="auth-btn" id="btn-register">
                        Daftar Sekarang
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>

                    <p style="font-size:.75rem;color:#94a3b8;text-align:center;margin:0;line-height:1.5;">
                        🔒 Pendaftaran ini khusus untuk calon murid. Akun guru dan admin dibuat oleh pengelola lembaga.
                    </p>
                </form>

                <p class="auth-footer-text">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="auth-link">Masuk di sini</a>
                </p>
            </div>

        </div>
    </div>
</section>
@endsection
