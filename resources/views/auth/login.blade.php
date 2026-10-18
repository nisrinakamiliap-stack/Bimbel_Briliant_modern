@extends('layouts.app')

@section('title', 'Login — Bimbel Sentra Edukasi')
@section('meta_description', 'Masuk ke akun Bimbel Sentra Edukasi untuk mengakses dashboard belajar, jadwal kelas, materi, dan nilai.')

@push('styles')
    @include('auth.partials.styles')
@endpush

@section('content')
<section class="auth-section">
    <div class="container-main">
        <div class="auth-grid">

            {{-- Panel Identitas Brand (desktop) --}}
            <aside class="auth-brand-panel" aria-hidden="true">
                <span class="auth-badge">Portal Belajar</span>
                <h2>Selamat datang kembali di Bimbel Sentra Edukasi</h2>
                <p>Lanjutkan perjalanan belajarmu. Akses jadwal kelas, materi, tugas, dan perkembangan nilai dalam satu tempat.</p>
                <ul class="auth-brand-list">
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Jadwal kelas dan materi yang selalu terbarui
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Pantau tugas, ujian, dan nilai secara langsung
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Satu akun untuk murid, guru, dan admin
                    </li>
                </ul>
            </aside>

            {{-- Kartu Form Login --}}
            <div class="auth-card">
                <h1>Masuk ke Akun Anda</h1>
                <p class="auth-subtitle">Gunakan email dan password yang telah terdaftar.</p>

                @if (session('success'))
                    <div class="auth-alert auth-alert--success" role="status">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @error('email')
                    <div class="auth-alert auth-alert--error" role="alert">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <form action="{{ route('login.store') }}" method="POST" class="auth-form" id="form-login" novalidate>
                    @csrf

                    {{-- Email --}}
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
                               autofocus
                               @error('email') aria-invalid="true" @enderror>
                    </div>

                    {{-- Password --}}
                    <x-password-input name="password"
                                      label="Password"
                                      autocomplete="current-password"
                                      placeholder="Masukkan password" />

                    <div class="auth-row">
                        <label class="auth-checkbox" for="remember">
                            <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            Ingat saya
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="auth-link" style="font-size:.875rem;">Lupa password?</a>
                        @endif
                    </div>

                    <button type="submit" class="auth-btn" id="btn-login">
                        Masuk
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                    </button>
                </form>

                <p class="auth-footer-text">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="auth-link">Daftar sebagai murid</a>
                </p>
            </div>

        </div>
    </div>
</section>
@endsection
