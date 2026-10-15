<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Bimbel Modern Brilliant — Lembaga bimbingan belajar terstruktur untuk siswa SD, SMP, SMA, dan persiapan ujian.')">
    <meta name="keywords" content="bimbingan belajar, bimbel, modern brilliant, les privat, SD, SMP, SMA, persiapan ujian, SNBT">
    <meta property="og:title" content="@yield('title', 'Bimbel Modern Brilliant — Belajar Lebih Terarah')">
    <meta property="og:description" content="@yield('meta_description', 'Temukan pengalaman belajar terstruktur bersama tutor profesional dan program yang sesuai dengan target prestasimu.')">
    <meta property="og:type" content="website">

    <title>@yield('title', 'Bimbel Modern Brilliant — Belajar Lebih Terarah, Raih Prestasi Lebih Gemilang')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @endif

    @stack('styles')
</head>
<body class="bg-white text-[#253247] antialiased">

{{-- NAVBAR --}}
<nav id="navbar" role="navigation" aria-label="Navigasi Utama">
    <div class="container-main">
        <div style="display:flex;align-items:center;justify-content:space-between;height:4.5rem;">
            {{-- Logo --}}
            <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:.625rem;text-decoration:none;flex-shrink:0;" aria-label="Bimbel Modern Brilliant — Beranda">
                <div style="width:2.25rem;height:2.25rem;background-color:#16845B;border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="white"/>
                        <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <span style="font-size:1rem;font-weight:800;color:#142B4A;display:block;line-height:1.1;">Bimbel</span>
                    <span style="font-size:.6875rem;font-weight:600;color:#16845B;letter-spacing:.05em;text-transform:uppercase;display:block;line-height:1.1;">MODERN BRILLIANT</span>
                </div>
            </a>

            {{-- Menu Desktop --}}
            <div id="desktop-nav" style="display:none;align-items:center;gap:.25rem;" role="menubar">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" role="menuitem">Home</a>
                <a href="{{ route('program.index') }}" class="nav-link {{ request()->routeIs('program.*') ? 'active' : '' }}" role="menuitem">Program Belajar</a>
                <a href="{{ route('jadwal.index') }}" class="nav-link {{ request()->routeIs('jadwal.*') ? 'active' : '' }}" role="menuitem">Jadwal</a>
                <a href="{{ route('tentang.index') }}" class="nav-link {{ request()->routeIs('tentang.*') ? 'active' : '' }}" role="menuitem">Tentang Kami</a>
                <a href="{{ route('kontak.index') }}" class="nav-link {{ request()->routeIs('kontak.*') ? 'active' : '' }}" role="menuitem">Kontak</a>
            </div>

            {{-- Right CTA Desktop --}}
            <div id="desktop-cta" style="display:none;align-items:center;gap:.75rem;">
                <a href="{{ route('kontak.index') }}" class="nav-link" style="font-weight:500;">Masuk</a>
                <a href="{{ route('program.index') }}" class="btn-primary btn-sm">Daftar Sekarang</a>
            </div>

            {{-- Hamburger Button Mobile --}}
            <button id="hamburger-btn" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="mobile-menu"
                style="display:flex;flex-direction:column;gap:5px;padding:.5rem;border:none;background:none;cursor:pointer;border-radius:.375rem;">
                <span id="ham-line-1" style="display:block;width:22px;height:2px;background-color:#142B4A;border-radius:1px;transition:transform .25s ease,opacity .25s ease;"></span>
                <span id="ham-line-2" style="display:block;width:22px;height:2px;background-color:#142B4A;border-radius:1px;transition:transform .25s ease,opacity .25s ease;"></span>
                <span id="ham-line-3" style="display:block;width:22px;height:2px;background-color:#142B4A;border-radius:1px;transition:transform .25s ease,opacity .25s ease;"></span>
            </button>
        </div>
    </div>
</nav>

{{-- MOBILE MENU DIALOG --}}
<div id="mobile-menu" role="dialog" aria-label="Menu Navigasi Mobile" aria-modal="true">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;padding-bottom:1.25rem;border-bottom:1px solid #e2e8f0;">
        <div style="display:flex;align-items:center;gap:.625rem;">
            <div style="width:2.25rem;height:2.25rem;background-color:#16845B;border-radius:.625rem;display:flex;align-items:center;justify-content:center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="white"/>
                    <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <span style="font-size:1rem;font-weight:800;color:#142B4A;display:block;line-height:1.1;">Bimbel</span>
                <span style="font-size:.6875rem;font-weight:600;color:#16845B;letter-spacing:.05em;text-transform:uppercase;display:block;line-height:1.1;">MODERN BRILLIANT</span>
            </div>
        </div>
        <button id="close-menu-btn" aria-label="Tutup menu" style="padding:.5rem;border:none;background:none;cursor:pointer;color:#4a5568;border-radius:.375rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    <nav role="menu" aria-label="Menu Mobile">
        <a href="{{ route('home') }}" class="mobile-nav-link {{ request()->routeIs('home') ? 'style-active' : '' }}" role="menuitem" style="{{ request()->routeIs('home') ? 'color:#16845B;font-weight:700;' : '' }}">Home</a>
        <a href="{{ route('program.index') }}" class="mobile-nav-link" role="menuitem" style="{{ request()->routeIs('program.*') ? 'color:#16845B;font-weight:700;' : '' }}">Program Belajar</a>
        <a href="{{ route('jadwal.index') }}" class="mobile-nav-link" role="menuitem" style="{{ request()->routeIs('jadwal.*') ? 'color:#16845B;font-weight:700;' : '' }}">Jadwal</a>
        <a href="{{ route('tentang.index') }}" class="mobile-nav-link" role="menuitem" style="{{ request()->routeIs('tentang.*') ? 'color:#16845B;font-weight:700;' : '' }}">Tentang Kami</a>
        <a href="{{ route('kontak.index') }}" class="mobile-nav-link" role="menuitem" style="{{ request()->routeIs('kontak.*') ? 'color:#16845B;font-weight:700;' : '' }}">Kontak</a>
    </nav>
    <div style="margin-top:2rem;display:flex;flex-direction:column;gap:.875rem;">
        <a href="{{ route('kontak.index') }}" class="btn-secondary" style="justify-content:center;">Masuk</a>
        <a href="{{ route('program.index') }}" class="btn-primary" style="justify-content:center;">
            Daftar Sekarang
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>
    </div>
</div>

{{-- MAIN CONTENT --}}
<main id="main-content" style="padding-top:4.5rem;min-height: calc(100vh - 4.5rem);">
    @yield('content')
    {{ $slot ?? '' }}
</main>

{{-- FOOTER SEDERHANA --}}
<footer id="footer" role="contentinfo" style="background-color:#0e1f36;color:rgba(255,255,255,0.7);padding-top:4rem;">
    <div class="container-main">
        <div id="footer-grid" style="display:grid;grid-template-columns:1fr;gap:2.5rem;padding-bottom:3rem;">
            {{-- Brand info --}}
            <div style="max-width:380px;">
                <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:1.25rem;">
                    <div style="width:2.25rem;height:2.25rem;background-color:#16845B;border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="white"/>
                            <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <span style="font-size:1rem;font-weight:800;color:#ffffff;display:block;line-height:1.1;">Bimbel</span>
                        <span style="font-size:.6875rem;font-weight:600;color:#16845B;letter-spacing:.05em;text-transform:uppercase;display:block;line-height:1.1;">MODERN BRILLIANT</span>
                    </div>
                </div>
                <p style="font-size:.875rem;color:rgba(255,255,255,.6);line-height:1.7;margin-bottom:1.5rem;">
                    Lembaga bimbingan belajar dengan pendekatan modern, materi terstruktur, dan pendampingan tutor berpengalaman untuk mengantarkan siswa meraih prestasi akademik terbaik.
                </p>
                <div style="display:flex;gap:.75rem;">
                    <a href="#" class="footer-social" aria-label="Instagram Bimbel Modern Brilliant">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="Facebook Bimbel Modern Brilliant">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="YouTube Bimbel Modern Brilliant">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"></path><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"></polygon></svg>
                    </a>
                </div>
            </div>

            {{-- Navigasi --}}
            <nav aria-label="Navigasi footer">
                <h3 style="font-size:.8125rem;font-weight:700;color:#ffffff;letter-spacing:.05em;text-transform:uppercase;margin-bottom:1.25rem;">Navigasi</h3>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.75rem;">
                    <li><a href="{{ route('home') }}" class="footer-link">Home</a></li>
                    <li><a href="{{ route('program.index') }}" class="footer-link">Program Belajar</a></li>
                    <li><a href="{{ route('jadwal.index') }}" class="footer-link">Jadwal</a></li>
                    <li><a href="{{ route('tentang.index') }}" class="footer-link">Tentang Kami</a></li>
                    <li><a href="{{ route('kontak.index') }}" class="footer-link">Kontak</a></li>
                </ul>
            </nav>

            {{-- Kontak --}}
            <address style="font-style:normal;">
                <h3 style="font-size:.8125rem;font-weight:700;color:#ffffff;letter-spacing:.05em;text-transform:uppercase;margin-bottom:1.25rem;">Informasi Kontak</h3>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.875rem;">
                    <li style="display:flex;align-items:flex-start;gap:.75rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span style="font-size:.875rem;color:rgba(255,255,255,.55);">Jl. Pendidikan Utama No. 88<br>Jakarta Selatan — 12340</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:.75rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <span style="font-size:.875rem;color:rgba(255,255,255,.55);">(021) 7890-1234 / 0812-3456-7890</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:.75rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <span style="font-size:.875rem;color:rgba(255,255,255,.55);">info@modernbrilliant.sch.id</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:.75rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span style="font-size:.875rem;color:rgba(255,255,255,.55);">Senin – Sabtu, 08.00 – 20.00 WIB</span>
                    </li>
                </ul>
            </address>
        </div>

        {{-- Bottom line --}}
        <div style="border-top:1px solid rgba(255,255,255,.08);padding:1.5rem 0;display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between;">
            <p style="font-size:.8125rem;color:rgba(255,255,255,.4);margin:0;">&copy; {{ date('Y') }} Bimbel Modern Brilliant. Hak cipta dilindungi.</p>
            <div style="display:flex;gap:1.5rem;">
                <a href="#" class="footer-link" style="font-size:.8125rem;color:rgba(255,255,255,.4);">Kebijakan Privasi</a>
                <a href="#" class="footer-link" style="font-size:.8125rem;color:rgba(255,255,255,.4);">Syarat &amp; Ketentuan</a>
            </div>
        </div>
    </div>
</footer>

{{-- SCRIPT NAVBAR & RESPONSIVENESS --}}
<script>
(function () {
    'use strict';
    var desktopNav = document.getElementById('desktop-nav');
    var desktopCta = document.getElementById('desktop-cta');
    var hamburgerBtn = document.getElementById('hamburger-btn');
    var mobileMenu = document.getElementById('mobile-menu');
    var closeMenuBtn = document.getElementById('close-menu-btn');
    var hamLine1 = document.getElementById('ham-line-1');
    var hamLine2 = document.getElementById('ham-line-2');
    var hamLine3 = document.getElementById('ham-line-3');
    var navbar = document.getElementById('navbar');

    function handleResize() {
        if (!desktopNav || !hamburgerBtn) return;
        if (window.innerWidth >= 768) {
            desktopNav.style.display = 'flex';
            if (desktopCta) desktopCta.style.display = 'flex';
            hamburgerBtn.style.display = 'none';
            closeMobileMenu();
        } else {
            desktopNav.style.display = 'none';
            if (desktopCta) desktopCta.style.display = 'none';
            hamburgerBtn.style.display = 'flex';
        }

        var footerGrid = document.getElementById('footer-grid');
        if (footerGrid) {
            var w = window.innerWidth;
            footerGrid.style.gridTemplateColumns = w >= 1024 ? '2fr 1fr 1.5fr' : (w >= 640 ? 'repeat(2,1fr)' : '1fr');
        }
    }

    function openMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.add('open');
        document.body.style.overflow = 'hidden';
        if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', 'true');
        if (hamLine1) hamLine1.style.transform = 'translateY(7px) rotate(45deg)';
        if (hamLine2) hamLine2.style.opacity = '0';
        if (hamLine3) hamLine3.style.transform = 'translateY(-7px) rotate(-45deg)';
        var firstLink = mobileMenu.querySelector('.mobile-nav-link');
        if (firstLink) firstLink.focus();
    }

    function closeMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.remove('open');
        document.body.style.overflow = '';
        if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', 'false');
        if (hamLine1) hamLine1.style.transform = '';
        if (hamLine2) hamLine2.style.opacity = '1';
        if (hamLine3) hamLine3.style.transform = '';
    }

    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function () {
            if (mobileMenu && mobileMenu.classList.contains('open')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
    }

    if (closeMenuBtn) {
        closeMenuBtn.addEventListener('click', closeMobileMenu);
    }

    document.querySelectorAll('.mobile-nav-link').forEach(function (l) {
        l.addEventListener('click', closeMobileMenu);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mobileMenu && mobileMenu.classList.contains('open')) {
            closeMobileMenu();
            if (hamburgerBtn) hamburgerBtn.focus();
        }
    });

    if (navbar) {
        window.addEventListener('scroll', function () {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        }, { passive: true });
    }

    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleResize, 100);
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', handleResize);
    } else {
        handleResize();
    }
}());
</script>

@stack('scripts')
</body>
</html>
