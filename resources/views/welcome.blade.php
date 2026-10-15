<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Bimbel Modern Brilliant — Lembaga bimbingan belajar yang menyediakan program pendampingan terstruktur untuk siswa SD, SMP, SMA, dan persiapan ujian.">
    <meta name="keywords" content="bimbingan belajar, bimbel, les privat, tutor, SD, SMP, SMA, persiapan ujian">
    <meta property="og:title" content="Bimbel Modern Brilliant — Belajar Lebih Terarah">
    <meta property="og:description" content="Temukan pengalaman belajar yang lebih terstruktur dengan pendampingan tutor profesional dan program yang sesuai kebutuhanmu.">
    <meta property="og:type" content="website">
    <title>Bimbel Modern Brilliant — Belajar Lebih Terarah, Raih Prestasi Lebih Gemilang</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&amp;display=swap" rel="stylesheet">
    @endif
</head>
<body>

{{-- NAVBAR --}}
<nav id="navbar" role="navigation" aria-label="Navigasi Utama">
    <div class="container-main">
        <div style="display:flex;align-items:center;justify-content:space-between;height:4.5rem;">
            <a href="#hero" style="display:flex;align-items:center;gap:.625rem;text-decoration:none;flex-shrink:0;" aria-label="Bimbel Modern Brilliant — Beranda">
                <div style="width:2.25rem;height:2.25rem;background-color:#16845B;border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="white"/>
                        <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <span style="font-size:1rem;font-weight:800;color:#142B4A;display:block;line-height:1.1;">Bimbel</span>
                    <span style="font-size:.6875rem;font-weight:600;color:#16845B;letter-spacing:.05em;text-transform:uppercase;display:block;line-height:1.1;">Modern Brilliant</span>
                </div>
            </a>
            <div id="desktop-nav" style="display:none;align-items:center;gap:.25rem;" role="menubar">
                <a href="#hero" class="nav-link active" role="menuitem">Home</a>
                <a href="{{ route('program.index') }}" class="nav-link" role="menuitem">Program Belajar</a>
                <a href="{{ route('jadwal.index') }}" class="nav-link" role="menuitem">Jadwal</a>
                <a href="#about" class="nav-link" role="menuitem">Tentang Kami</a>
                <a href="#contact" class="nav-link" role="menuitem">Kontak</a>
            </div>
            <div id="desktop-cta" style="display:none;align-items:center;gap:.75rem;">
                <a href="#contact" class="nav-link" style="font-weight:500;">Masuk</a>
                <a href="#programs" class="btn-primary btn-sm">Daftar Sekarang</a>
            </div>
            <button id="hamburger-btn" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="mobile-menu"
                style="display:flex;flex-direction:column;gap:5px;padding:.5rem;border:none;background:none;cursor:pointer;border-radius:.375rem;">
                <span id="ham-line-1" style="display:block;width:22px;height:2px;background-color:#142B4A;border-radius:1px;transition:transform .25s ease,opacity .25s ease;"></span>
                <span id="ham-line-2" style="display:block;width:22px;height:2px;background-color:#142B4A;border-radius:1px;transition:transform .25s ease,opacity .25s ease;"></span>
                <span id="ham-line-3" style="display:block;width:22px;height:2px;background-color:#142B4A;border-radius:1px;transition:transform .25s ease,opacity .25s ease;"></span>
            </button>
        </div>
    </div>
</nav>

{{-- MOBILE MENU --}}
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
                <span style="font-size:.6875rem;font-weight:600;color:#16845B;letter-spacing:.05em;text-transform:uppercase;display:block;line-height:1.1;">Modern Brilliant</span>
            </div>
        </div>
        <button id="close-menu-btn" aria-label="Tutup menu" style="padding:.5rem;border:none;background:none;cursor:pointer;color:#4a5568;border-radius:.375rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    <nav role="menu" aria-label="Menu Mobile">
        <a href="#hero" class="mobile-nav-link" role="menuitem">Home</a>
        <a href="{{ route('program.index') }}" class="mobile-nav-link" role="menuitem">Program Belajar</a>
        <a href="{{ route('jadwal.index') }}" class="mobile-nav-link" role="menuitem">Jadwal</a>
        <a href="#about" class="mobile-nav-link" role="menuitem">Tentang Kami</a>
        <a href="#contact" class="mobile-nav-link" role="menuitem">Kontak</a>
    </nav>
    <div style="margin-top:2rem;display:flex;flex-direction:column;gap:.875rem;">
        <a href="#contact" class="btn-secondary" style="justify-content:center;">Masuk</a>
        <a href="#programs" class="btn-primary" style="justify-content:center;">
            Daftar Sekarang
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
    </div>
</div>

{{-- HERO SECTION --}}
<section id="hero" aria-labelledby="hero-heading">
    <div class="hero-deco-circle" style="width:500px;height:500px;top:-100px;right:-150px;" aria-hidden="true"></div>
    <div class="hero-deco-circle" style="width:250px;height:250px;bottom:-50px;left:-80px;opacity:.4;" aria-hidden="true"></div>
    <div class="container-main" style="position:relative;z-index:1;">
        <div id="hero-grid" style="display:grid;grid-template-columns:1fr;gap:3rem;align-items:center;">
            <div style="max-width:640px;">
                <div class="hero-badge animate-fade-in">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Pendampingan Belajar Terarah
                </div>
                <h1 id="hero-heading" class="heading-display animate-fade-in-up delay-100" style="margin-bottom:1.25rem;opacity:0;">
                    Belajar Lebih Terarah,<br>
                    <span style="color:#16845B;">Raih Prestasi</span><br>
                    Lebih Gemilang.
                </h1>
                <p class="animate-fade-in-up delay-200" style="font-size:1.0625rem;color:#4a5568;line-height:1.75;margin-bottom:2rem;max-width:520px;opacity:0;">
                    Temukan pengalaman belajar yang lebih terstruktur dengan pendampingan tutor, program belajar yang sesuai kebutuhan, dan lingkungan yang mendukung perkembangan akademikmu.
                </p>
                <div class="animate-fade-in-up delay-300" style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:2.5rem;opacity:0;">
                    <a href="{{ route('program.index') }}" class="btn-primary" id="cta-jelajahi-program">
                        Jelajahi Program
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                    <a href="#contact" class="btn-secondary" id="cta-konsultasi">Konsultasi Gratis</a>
                </div>
                <div class="animate-fade-in-up delay-400" style="display:flex;flex-wrap:wrap;gap:1.25rem;opacity:0;">
                    @foreach ([['label' => 'Tutor Berpengalaman'], ['label' => 'Jadwal Fleksibel'], ['label' => 'Materi Terstruktur']] as $item)
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <div style="width:1.75rem;height:1.75rem;background-color:#EAF6EF;border-radius:50%;display:flex;align-items:center;justify-content:center;" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span style="font-size:.8125rem;font-weight:500;color:#4a5568;">{{ $item['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div id="hero-image-col" style="position:relative;">
                <div class="hero-image-wrapper" style="aspect-ratio:4/3;">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&q=80"
                         alt="Siswa berdiskusi bersama tutor dalam sesi bimbingan belajar"
                         style="width:100%;height:100%;object-fit:cover;display:block;" loading="eager">
                </div>
                <a href="{{ route('program.index') }}" style="position:absolute;bottom:-1rem;left:-1rem;background-color:white;border-radius:.875rem;padding:1rem 1.25rem;box-shadow:0 12px 32px rgba(20,43,74,.12);display:flex;align-items:center;gap:.75rem;min-width:200px;text-decoration:none;transition:transform .2s ease,box-shadow .2s ease;cursor:pointer;" id="hero-floating-program-card" aria-label="Lihat Program Belajar">
                    <div style="width:2.5rem;height:2.5rem;background-color:#EAF6EF;border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:.8125rem;font-weight:700;color:#142B4A;">Program Tersedia</div>
                        <div style="font-size:.75rem;color:#4a5568;">SD, SMP, SMA &amp; Ujian</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- TRUST INDICATORS --}}
<section id="trust-indicators" aria-label="Indikator Kepercayaan">
    <div class="container-main">
        <div id="trust-grid" style="display:grid;grid-template-columns:1fr;gap:0;">
            @php
            $trustItems = [
                ['icon' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>', 'title' => 'Program Belajar Terstruktur', 'desc' => 'Kurikulum sesuai jenjang pendidikan'],
                ['icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>', 'title' => 'Pendampingan Tutor', 'desc' => 'Tutor berdedikasi dan sabar'],
                ['icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>', 'title' => 'Pilihan Jadwal Belajar', 'desc' => 'Fleksibel sesuai ketersediaan waktu'],
                ['icon' => '<line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line>', 'title' => 'Evaluasi Perkembangan', 'desc' => 'Laporan berkala kemajuan belajar siswa'],
            ];
            @endphp
            @foreach ($trustItems as $i => $item)
            <div class="trust-item" id="trust-{{ $i + 1 }}">
                <div class="trust-icon-wrapper" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                </div>
                <div>
                    <div style="font-size:.875rem;font-weight:700;color:#142B4A;margin-bottom:.125rem;">{{ $item['title'] }}</div>
                    <div style="font-size:.75rem;color:#64748b;">{{ $item['desc'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- PROGRAM BELAJAR --}}
<section id="programs" class="section-padding" aria-labelledby="programs-heading">
    <div class="container-main">
        <div style="text-align:center;max-width:580px;margin:0 auto 3.5rem;">
            <div class="divider-green" style="margin-left:auto;margin-right:auto;"></div>
            <p class="text-section-label" style="margin-bottom:.75rem;">Program Kami</p>
            <h2 id="programs-heading" class="heading-xl" style="margin-bottom:1rem;">Temukan Program Belajar yang Tepat</h2>
            <p style="font-size:1rem;color:#4a5568;line-height:1.7;">Pilih program sesuai kebutuhan dan target belajarmu. Setiap program dirancang untuk membantu siswa belajar lebih terarah.</p>
        </div>
        @php
        $programs = [
            ['id'=>'sd','title'=>'Bimbingan Belajar SD','badge'=>'Kelas 1–6','badge_class'=>'badge-green','desc'=>'Membangun fondasi belajar yang kuat dengan pendekatan yang menyenangkan dan sesuai usia. Fokus pada Matematika, Bahasa Indonesia, IPA, dan IPS.','feat1'=>'Target: Siswa SD kelas 1–6','feat2'=>'Sesi kelompok kecil & individual','icon_bg'=>'#EAF6EF','icon_color'=>'#16845B','icon'=>'<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>'],
            ['id'=>'smp','title'=>'Bimbingan Belajar SMP','badge'=>'Kelas 7–9','badge_class'=>'badge-navy','desc'=>'Membantu siswa SMP menguasai mata pelajaran inti dengan metode belajar yang lebih terstruktur. Persiapan ujian akhir semester dan ujian nasional.','feat1'=>'Target: Siswa SMP kelas 7–9','feat2'=>'Matematika, IPA, Bahasa Inggris','icon_bg'=>'rgba(20,43,74,0.07)','icon_color'=>'#142B4A','icon'=>'<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>'],
            ['id'=>'sma','title'=>'Bimbingan Belajar SMA','badge'=>'Kelas 10–12','badge_class'=>'badge-green','desc'=>'Program intensif untuk siswa SMA dengan fokus penguasaan materi dan strategi belajar efektif menghadapi ujian akhir dan seleksi perguruan tinggi.','feat1'=>'Target: Siswa SMA kelas 10–12','feat2'=>'IPA, IPS, dan jurusan pilihan','icon_bg'=>'#EAF6EF','icon_color'=>'#16845B','icon'=>'<path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path>'],
            ['id'=>'ujian','title'=>'Persiapan Ujian','badge'=>null,'badge_class'=>'','popular'=>true,'desc'=>'Program khusus persiapan ujian seperti UN, SNBT, dan ujian masuk sekolah favorit. Latihan soal intensif dan pembahasan mendalam.','feat1'=>'UN, SNBT, Ujian Masuk Sekolah','feat2'=>'Simulasi & tryout berkala','icon_bg'=>'rgba(20,43,74,0.07)','icon_color'=>'#142B4A','icon'=>'<polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>'],
        ];
        @endphp
        <div class="grid-programs">
            @foreach ($programs as $program)
            <article class="program-card" id="program-{{ $program['id'] }}" style="{{ isset($program['popular']) ? 'border-color:rgba(22,132,91,.3);background-color:#fafffe;' : '' }}position:relative;">
                @if (isset($program['popular']))
                <div style="position:absolute;top:1.25rem;right:1.25rem;" aria-hidden="true">
                    <span class="badge-pill badge-green" style="font-size:.7rem;">Populer</span>
                </div>
                @endif
                <div class="program-card-icon" style="background-color:{{ $program['icon_bg'] }};" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $program['icon_color'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $program['icon'] !!}</svg>
                </div>
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem;">
                        <h3 style="font-size:1.125rem;font-weight:700;color:#142B4A;margin:0;">{{ $program['title'] }}</h3>
                        @if ($program['badge'])
                        <span class="badge-pill {{ $program['badge_class'] }}">{{ $program['badge'] }}</span>
                        @endif
                    </div>
                    <p style="font-size:.875rem;color:#4a5568;line-height:1.7;margin-bottom:1.25rem;">{{ $program['desc'] }}</p>
                    <div style="font-size:.8125rem;color:#64748b;margin-bottom:1.5rem;">
                        @foreach ([$program['feat1'], $program['feat2']] as $feat)
                        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.375rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>{{ $feat }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('program.show', $program['id'] === 'ujian' ? 'persiapan-ujian' : $program['id']) }}" class="btn-primary btn-sm" id="btn-program-{{ $program['id'] }}" style="width:100%;justify-content:center;">Lihat Program</a>
            </article>
            @endforeach
        </div>
    </div>
</section>

{{-- MENGAPA MEMILIH KAMI --}}
<section id="about" class="section-padding bg-section-light" aria-labelledby="about-heading">
    <div class="container-main">
        <div id="about-grid" style="display:grid;grid-template-columns:1fr;gap:4rem;align-items:center;">
            <div style="max-width:520px;">
                <div class="divider-green"></div>
                <p class="text-section-label" style="margin-bottom:.75rem;">Mengapa Kami</p>
                <h2 id="about-heading" class="heading-xl" style="margin-bottom:1.25rem;">Pendampingan Belajar yang Lebih Bermakna</h2>
                <p style="font-size:1rem;color:#4a5568;line-height:1.75;margin-bottom:2rem;">Kami percaya bahwa setiap siswa memiliki potensi yang perlu dikembangkan dengan pendekatan yang tepat, sabar, dan terstruktur.</p>
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    @foreach (['Program disesuaikan dengan kebutuhan dan kemampuan masing-masing siswa', 'Laporan perkembangan belajar yang transparan untuk orang tua', 'Lingkungan belajar yang nyaman dan mendukung'] as $point)
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <div style="width:1.5rem;height:1.5rem;background-color:#EAF6EF;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;" aria-hidden="true">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span style="font-size:.9375rem;color:#4a5568;">{{ $point }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @php
            $features = [
                ['icon'=>'<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>','title'=>'Pembelajaran Terstruktur','desc'=>'Materi disusun secara sistematis mengikuti kurikulum dengan pendalaman yang bertahap.'],
                ['icon'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>','title'=>'Pendampingan Tutor','desc'=>'Tutor sabar dan berpengalaman yang siap membantu siswa memahami materi secara mendalam.'],
                ['icon'=>'<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>','title'=>'Materi Sesuai Kebutuhan','desc'=>'Konten belajar yang relevan dengan kondisi dan tujuan belajar setiap siswa.'],
                ['icon'=>'<line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line>','title'=>'Evaluasi Perkembangan','desc'=>'Pemantauan berkala dengan laporan yang membantu orang tua memahami perkembangan putra-putrinya.'],
            ];
            @endphp
            <div class="grid-features">
                @foreach ($features as $i => $feat)
                <div class="feature-card" id="feature-{{ $i + 1 }}">
                    <div class="feature-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $feat['icon'] !!}</svg>
                    </div>
                    <div>
                        <h3 style="font-size:.9375rem;font-weight:700;color:#142B4A;margin-bottom:.375rem;">{{ $feat['title'] }}</h3>
                        <p style="font-size:.8125rem;color:#4a5568;line-height:1.6;margin:0;">{{ $feat['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- CARA KERJA --}}
<section id="cara-kerja" class="section-padding" aria-labelledby="cara-kerja-heading">
    <div class="container-main">
        <div style="text-align:center;max-width:520px;margin:0 auto 3.5rem;">
            <div class="divider-green" style="margin-left:auto;margin-right:auto;"></div>
            <p class="text-section-label" style="margin-bottom:.75rem;">Cara Kerja</p>
            <h2 id="cara-kerja-heading" class="heading-xl" style="margin-bottom:1rem;">Mulai Perjalanan Belajarmu dalam 3 Langkah</h2>
            <p style="font-size:1rem;color:#4a5568;line-height:1.7;">Proses yang mudah dan transparan untuk memulai bimbingan belajar bersama kami.</p>
        </div>
        @php
        $steps = [
            ['num'=>1,'title'=>'Pilih Program Belajar','desc'=>'Temukan program yang sesuai dengan jenjang pendidikan dan kebutuhan belajarmu dari pilihan yang tersedia.','icon'=>'<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>','connector'=>true],
            ['num'=>2,'title'=>'Konsultasikan Kebutuhan','desc'=>'Tim kami akan membantu menentukan jadwal dan rencana belajar yang paling sesuai dengan kebutuhan dan kesibukanmu.','icon'=>'<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>','connector'=>true],
            ['num'=>3,'title'=>'Mulai Belajar Bersama Tutor','desc'=>'Nikmati pengalaman belajar yang menyenangkan dan terarah bersama tutor berpengalaman kami.','icon'=>'<path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path>','connector'=>false],
        ];
        @endphp
        <div class="grid-steps">
            @foreach ($steps as $step)
            <div class="step-card" id="step-{{ $step['num'] }}" style="position:relative;">
                @if ($step['connector'])
                <div class="step-connector" id="step-connector-{{ $step['num'] }}" aria-hidden="true"></div>
                @endif
                <div style="display:flex;justify-content:center;margin-bottom:1.25rem;">
                    <div class="step-number" aria-hidden="true">{{ $step['num'] }}</div>
                </div>
                <div style="width:3.5rem;height:3.5rem;background-color:#EAF6EF;border-radius:.875rem;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $step['icon'] !!}</svg>
                </div>
                <h3 style="font-size:1.0625rem;font-weight:700;color:#142B4A;margin-bottom:.625rem;">{{ $step['title'] }}</h3>
                <p style="font-size:.875rem;color:#4a5568;line-height:1.7;max-width:220px;margin:0 auto;">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>
        <div style="text-align:center;margin-top:3rem;">
            <a href="#contact" class="btn-primary" id="cta-mulai-belajar">
                Mulai Sekarang
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        </div>
    </div>
</section>

{{-- TESTIMONI --}}
<section id="testimonials" class="section-padding bg-section-green" aria-labelledby="testimonials-heading">
    <div class="container-main">
        <div style="text-align:center;max-width:520px;margin:0 auto 3.5rem;">
            <div class="divider-green" style="margin-left:auto;margin-right:auto;"></div>
            <p class="text-section-label" style="margin-bottom:.75rem;">Testimoni</p>
            <h2 id="testimonials-heading" class="heading-xl" style="margin-bottom:1rem;">Cerita dari Pengalaman Belajar</h2>
            <p style="font-size:1rem;color:#4a5568;line-height:1.7;">Berikut adalah contoh cerita dari berbagai peserta. Gantikan dengan testimoni nyata dari siswa dan orang tua.</p>
            <div class="demo-label" style="margin:1rem auto 0;width:fit-content;" role="note" aria-label="Konten demo">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                Konten demo — gantikan dengan testimoni nyata
            </div>
        </div>
        @php
        $testimonials = [
            ['initials'=>'BU','quote'=>'Program bimbingan belajarnya sangat membantu anak saya memahami pelajaran yang selama ini sulit. Tutor-nya sabar dan cara mengajarnya mudah dipahami.','name'=>'[Nama Orang Tua]','role'=>'Orang tua siswa SD — Data Contoh'],
            ['initials'=>'SI','quote'=>'Jadwalnya fleksibel dan tutor selalu siap membantu ketika ada materi yang belum dipahami. Saya merasa lebih percaya diri menghadapi ujian.','name'=>'[Nama Siswa]','role'=>'Peserta Program SMP — Data Contoh'],
            ['initials'=>'AY','quote'=>'Program persiapan ujiannya sangat terstruktur. Latihan soal yang diberikan sangat relevan dan membantu saya belajar lebih fokus dan efisien.','name'=>'[Nama Siswa]','role'=>'Peserta Persiapan Ujian — Data Contoh'],
        ];
        @endphp
        <div class="grid-testimonials">
            @foreach ($testimonials as $i => $testimonial)
            <blockquote class="testimonial-card" id="testimonial-{{ $i + 1 }}">
                <div style="display:flex;margin-bottom:1rem;" aria-label="Rating: 5 bintang" role="img">
                    @for ($s = 0; $s < 5; $s++)
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1" aria-hidden="true">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    @endfor
                </div>
                <p class="testimonial-quote" style="margin-bottom:1.5rem;">"{{ $testimonial['quote'] }}"</p>
                <footer style="display:flex;align-items:center;gap:.875rem;margin-top:auto;">
                    <div class="testimonial-avatar" aria-hidden="true">{{ $testimonial['initials'] }}</div>
                    <div>
                        <div style="font-size:.875rem;font-weight:700;color:#142B4A;">{{ $testimonial['name'] }}</div>
                        <div style="font-size:.75rem;color:#64748b;">{{ $testimonial['role'] }}</div>
                    </div>
                </footer>
            </blockquote>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA SECTION --}}
<section id="cta-section" class="section-padding" aria-labelledby="cta-heading">
    <div class="container-main" style="position:relative;z-index:1;">
        <div style="text-align:center;max-width:600px;margin:0 auto;">
            <h2 id="cta-heading" style="font-size:clamp(1.75rem,4vw,2.75rem);font-weight:800;color:#ffffff;line-height:1.2;letter-spacing:-.02em;margin-bottom:1.25rem;">
                Siap Memulai Perjalanan Belajarmu?
            </h2>
            <p style="font-size:1.0625rem;color:rgba(255,255,255,.75);line-height:1.7;margin-bottom:2.5rem;">
                Konsultasikan kebutuhan belajarmu dan temukan program yang sesuai. Kami siap membantu kamu belajar lebih terarah.
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:center;">
                <a href="#contact" class="btn-primary" id="cta-daftar-sekarang" style="background-color:#16845B;border-color:#16845B;">
                    Daftar Sekarang
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
                <a href="#contact" class="btn-outline-white" id="cta-hubungi-kami">Hubungi Kami</a>
            </div>
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer id="footer" aria-label="Footer">
    <div class="container-main">
        <div id="footer-grid" style="display:grid;grid-template-columns:1fr;gap:3rem;padding:4rem 0 3rem;">
            <div style="max-width:320px;">
                <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:1.25rem;">
                    <div style="width:2.25rem;height:2.25rem;background-color:#16845B;border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="white"/>
                            <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <span style="font-size:1rem;font-weight:800;color:#ffffff;display:block;line-height:1.1;">Bimbel</span>
                        <span style="font-size:.6875rem;font-weight:600;color:#16845B;letter-spacing:.05em;text-transform:uppercase;display:block;line-height:1.1;">Modern Brilliant</span>
                    </div>
                </div>
                <p style="font-size:.875rem;color:rgba(255,255,255,.55);line-height:1.7;margin-bottom:1.5rem;">Lembaga bimbingan belajar yang menyediakan program pendampingan terstruktur untuk siswa SD, SMP, SMA, dan persiapan ujian.</p>
                <div style="display:flex;gap:.625rem;" aria-label="Media sosial kami">
                    <a href="#" class="footer-social" aria-label="Instagram (placeholder)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="Facebook (placeholder)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="WhatsApp (placeholder)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="YouTube (placeholder)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"></path><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"></polygon></svg>
                    </a>
                </div>
            </div>
            <nav aria-label="Navigasi footer">
                <h3 style="font-size:.8125rem;font-weight:700;color:#ffffff;letter-spacing:.05em;text-transform:uppercase;margin-bottom:1.25rem;">Navigasi</h3>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.75rem;">
                    @foreach ([['href'=>'#hero','label'=>'Home'],['href'=>'#programs','label'=>'Program Belajar'],['href'=>'#cara-kerja','label'=>'Jadwal'],['href'=>'#about','label'=>'Tentang Kami'],['href'=>'#contact','label'=>'Kontak']] as $link)
                    <li><a href="{{ $link['href'] }}" class="footer-link">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>
            <address id="contact" style="font-style:normal;">
                <h3 style="font-size:.8125rem;font-weight:700;color:#ffffff;letter-spacing:.05em;text-transform:uppercase;margin-bottom:1.25rem;">Informasi Kontak</h3>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.875rem;">
                    <li style="display:flex;align-items:flex-start;gap:.75rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span style="font-size:.875rem;color:rgba(255,255,255,.55);">[Alamat lengkap akan diisi]<br>Kota, Provinsi — Kode Pos</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:.75rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <span style="font-size:.875rem;color:rgba(255,255,255,.55);">[Nomor telepon akan diisi]</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:.75rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <span style="font-size:.875rem;color:rgba(255,255,255,.55);">[Email akan diisi]</span>
                    </li>
                    <li style="display:flex;align-items:center;gap:.75rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span style="font-size:.875rem;color:rgba(255,255,255,.55);">Senin – Sabtu, 07.00 – 20.00</span>
                    </li>
                </ul>
            </address>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,.08);padding:1.5rem 0;display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between;">
            <p style="font-size:.8125rem;color:rgba(255,255,255,.4);margin:0;">&copy; {{ date('Y') }} Bimbel Modern Brilliant. Hak cipta dilindungi.</p>
            <div style="display:flex;gap:1.5rem;">
                <a href="#" class="footer-link" style="font-size:.8125rem;color:rgba(255,255,255,.4);">Kebijakan Privasi</a>
                <a href="#" class="footer-link" style="font-size:.8125rem;color:rgba(255,255,255,.4);">Syarat &amp; Ketentuan</a>
            </div>
        </div>
    </div>
</footer>

{{-- JAVASCRIPT --}}
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
        if (window.innerWidth >= 768) {
            desktopNav.style.display = 'flex';
            desktopCta.style.display = 'flex';
            hamburgerBtn.style.display = 'none';
            closeMobileMenu();
        } else {
            desktopNav.style.display = 'none';
            desktopCta.style.display = 'none';
            hamburgerBtn.style.display = 'flex';
        }
    }

    function openMobileMenu() {
        mobileMenu.classList.add('open');
        document.body.style.overflow = 'hidden';
        hamburgerBtn.setAttribute('aria-expanded', 'true');
        hamLine1.style.transform = 'translateY(7px) rotate(45deg)';
        hamLine2.style.opacity = '0';
        hamLine3.style.transform = 'translateY(-7px) rotate(-45deg)';
        var firstLink = mobileMenu.querySelector('.mobile-nav-link');
        if (firstLink) firstLink.focus();
    }

    function closeMobileMenu() {
        mobileMenu.classList.remove('open');
        document.body.style.overflow = '';
        hamburgerBtn.setAttribute('aria-expanded', 'false');
        hamLine1.style.transform = '';
        hamLine2.style.opacity = '1';
        hamLine3.style.transform = '';
    }

    hamburgerBtn.addEventListener('click', function () {
        if (mobileMenu.classList.contains('open')) { closeMobileMenu(); } else { openMobileMenu(); }
    });
    closeMenuBtn.addEventListener('click', closeMobileMenu);
    document.querySelectorAll('.mobile-nav-link').forEach(function (l) { l.addEventListener('click', closeMobileMenu); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('open')) { closeMobileMenu(); hamburgerBtn.focus(); }
    });

    window.addEventListener('scroll', function () {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });

    var sections = document.querySelectorAll('section[id]');
    var navLinks = document.querySelectorAll('.nav-link');
    window.addEventListener('scroll', function () {
        var scrollPos = window.scrollY + 100;
        var activeId = '';
        sections.forEach(function (s) { if (s.offsetTop <= scrollPos) activeId = s.id; });
        navLinks.forEach(function (l) {
            l.classList.remove('active');
            if (l.getAttribute('href') === '#' + activeId) l.classList.add('active');
        });
    }, { passive: true });

    function updateGrids() {
        var w = window.innerWidth;
        var trustGrid = document.getElementById('trust-grid');
        var heroGrid = document.getElementById('hero-grid');
        var aboutGrid = document.getElementById('about-grid');
        var footerGrid = document.getElementById('footer-grid');

        if (trustGrid) {
            trustGrid.style.gridTemplateColumns = w >= 768 ? 'repeat(4,1fr)' : (w >= 480 ? 'repeat(2,1fr)' : '1fr');
        }
        if (heroGrid) heroGrid.style.gridTemplateColumns = w >= 768 ? '1fr 1fr' : '1fr';
        if (aboutGrid) aboutGrid.style.gridTemplateColumns = w >= 1024 ? '1fr 1fr' : '1fr';
        if (footerGrid) {
            footerGrid.style.gridTemplateColumns = w >= 1024 ? '2fr 1fr 1.5fr' : (w >= 640 ? 'repeat(2,1fr)' : '1fr');
        }
        [1, 2].forEach(function (n) {
            var c = document.getElementById('step-connector-' + n);
            if (c) c.style.display = w >= 768 ? 'block' : 'none';
        });
    }

    function initAnimations() {
        if (!('IntersectionObserver' in window)) return;
        var els = document.querySelectorAll('.program-card,.feature-card,.step-card,.testimonial-card,.trust-item');
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.style.opacity = '1';
                    e.target.style.transform = 'translateY(0)';
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        els.forEach(function (el) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity .5s ease,transform .5s ease';
            obs.observe(el);
        });
    }

    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () { handleResize(); updateGrids(); }, 100);
    });

    function init() { handleResize(); updateGrids(); initAnimations(); }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
}());
</script>
</body>
</html>