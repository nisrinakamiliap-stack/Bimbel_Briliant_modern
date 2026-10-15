@extends('layouts.app')

@section('title', 'Tentang Kami — Bimbel Modern Brilliant')
@section('meta_description', 'Kenali lebih dekat Bimbel Modern Brilliant: lembaga bimbingan belajar dengan pendekatan personal, pemahaman konsep mendalam, dan tutor lulusan PTN terbaik sejak 2018.')

@section('content')
{{-- HERO / HEADER SECTION --}}
<section style="background-color: #ffffff; padding-top: 2.5rem; padding-bottom: 3.5rem; border-bottom: 1px solid #f1f5f9;">
    <div class="container-main">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" style="margin-bottom: 1.5rem;">
            <ol style="display: flex; align-items: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.875rem;">
                <li>
                    <a href="{{ route('home') }}" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#16845B'" onmouseout="this.style.color='#64748b'">Beranda</a>
                </li>
                <li style="color: #cbd5e1;" aria-hidden="true">/</li>
                <li style="color: #142B4A; font-weight: 600;" aria-current="page">Tentang Kami</li>
            </ol>
        </nav>

        {{-- Header Title & Subtitle --}}
        <div style="text-align: center; max-width: 760px; margin: 0 auto;">
            <div class="divider-green" style="margin-left: auto; margin-right: auto;"></div>
            <p class="text-section-label" style="margin-bottom: 0.75rem;">Profil Lembaga</p>
            <h1 class="heading-xl" style="margin-bottom: 1rem; color: #142B4A;">
                {{ $about['profil']['judul'] ?? 'Membimbing Generasi Cerdas dan Berkarakter Sejak 2018' }}
            </h1>
            <p style="font-size: 1.0625rem; color: #4a5568; line-height: 1.75; margin: 0 auto;">
                {{ $about['profil']['ringkasan'] ?? 'Temukan dedikasi kami dalam menghadirkan pengalaman belajar yang personal, terstruktur, dan bermakna bagi masa depan putra-putri Anda.' }}
            </p>
        </div>
    </div>
</section>

{{-- STATISTIK UTAMA --}}
<section style="padding-top: 3.5rem; padding-bottom: 3.5rem; background-color: #F8FAFC; border-bottom: 1px solid #e2e8f0;">
    <div class="container-main">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
            @foreach ($about['statistik'] ?? [] as $stat)
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.75rem 1.5rem; text-align: center; box-shadow: 0 4px 12px rgba(20,43,74,0.03); transition: transform 0.2s ease, border-color 0.2s ease;"
                     onmouseover="this.style.transform='translateY(-3px)';this.style.borderColor='#16845B';"
                     onmouseout="this.style.transform='translateY(0)';this.style.borderColor='#e2e8f0';">
                    <div style="font-size: 2.25rem; font-weight: 800; color: #16845B; line-height: 1.1; margin-bottom: 0.5rem;">
                        {{ $stat['angka'] }}
                    </div>
                    <div style="font-size: 1rem; font-weight: 700; color: #142B4A; margin-bottom: 0.25rem;">
                        {{ $stat['label'] }}
                    </div>
                    <div style="font-size: 0.8125rem; color: #64748b; line-height: 1.4;">
                        {{ $stat['sub'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CERITA & FILOSOFI --}}
<section style="padding-top: 5rem; padding-bottom: 5rem; background-color: #ffffff;">
    <div class="container-main">
        <div style="display: grid; grid-template-columns: 1fr; gap: 3.5rem; align-items: center;" id="about-story-grid">
            <div>
                <div class="divider-green"></div>
                <p class="text-section-label" style="margin-bottom: 0.75rem;">Cerita Kami</p>
                <h2 class="heading-lg" style="margin-bottom: 1.25rem; color: #142B4A;">
                    Pendidikan yang Memahami dan Menumbuhkan
                </h2>
                <p style="font-size: 1rem; color: #4a5568; line-height: 1.8; margin-bottom: 1.25rem;">
                    {{ $about['profil']['cerita'] ?? '' }}
                </p>
                <p style="font-size: 1rem; color: #4a5568; line-height: 1.8; margin-bottom: 2rem;">
                    Kami tidak memandang siswa sebagai wadah kosong yang harus dijejali rumus instan. Di Modern Brilliant, tutor memposisikan diri sebagai mentor, sahabat diskusi, dan fasilitator yang memandu anak memahami esensi ilmu dengan hati yang gembira.
                </p>

                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    <a href="{{ route('program.index') }}" class="btn-primary" style="border-radius: 0.5rem;">
                        Lihat Program Kami
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                    <a href="{{ route('jadwal.index') }}" class="btn-secondary" style="border-radius: 0.5rem;">
                        Cek Jadwal Kelas
                    </a>
                </div>
            </div>

            {{-- Kolom Kanan: Card Sorotan --}}
            <div>
                <div style="background: linear-gradient(135deg, #142B4A 0%, #1d3d6b 100%); border-radius: 1.25rem; padding: 2.5rem; color: #ffffff; box-shadow: 0 16px 40px rgba(20,43,74,0.12); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -30px; right: -30px; width: 150px; height: 150px; border-radius: 50%; background-color: rgba(22,132,91,0.2); pointer-events: none;"></div>
                    <div style="position: relative; z-index: 1;">
                        <span class="badge-pill" style="background-color: rgba(22,132,91,0.25); color: #86efac; border: 1px solid rgba(134,239,172,0.3); font-size: 0.75rem; margin-bottom: 1.25rem;">
                            Filosofi Pendidikan
                        </span>
                        <blockquote style="font-size: 1.125rem; font-style: italic; line-height: 1.8; color: rgba(255,255,255,0.95); margin: 0 0 1.5rem 0;">
                            &ldquo;Ketika seorang anak memahami konsep dengan benar, ia tidak hanya lulus ujian—ia mendapatkan kunci untuk membuka potensi tak terbatas di masa depannya.&rdquo;
                        </blockquote>
                        <div style="display: flex; align-items: center; gap: 0.875rem; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 1.25rem;">
                            <div style="width: 2.75rem; height: 2.75rem; border-radius: 50%; background-color: #16845B; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.875rem; color: #ffffff;">
                                MB
                            </div>
                            <div>
                                <div style="font-size: 0.9375rem; font-weight: 700; color: #ffffff;">Dewan Pembina Akademik</div>
                                <div style="font-size: 0.8125rem; color: rgba(255,255,255,0.7);">Bimbel Modern Brilliant</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- VISI & MISI --}}
<section style="padding-top: 5rem; padding-bottom: 5rem; background-color: #F8FAFC; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
    <div class="container-main">
        <div style="text-align: center; max-width: 600px; margin: 0 auto 3.5rem;">
            <div class="divider-green" style="margin-left: auto; margin-right: auto;"></div>
            <p class="text-section-label" style="margin-bottom: 0.75rem;">Landasan Kami</p>
            <h2 class="heading-xl" style="color: #142B4A; margin-bottom: 0.75rem;">Visi &amp; Misi Lembaga</h2>
            <p style="font-size: 1rem; color: #64748b; line-height: 1.6;">Arah dan komitmen nyata yang senantiasa menuntun setiap proses bimbingan kami.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;" id="vision-mission-grid">
            {{-- Kartu Visi --}}
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2.5rem; box-shadow: 0 4px 16px rgba(20,43,74,0.03);">
                <div style="display: flex; align-items: center; gap: 0.875rem; margin-bottom: 1.25rem;">
                    <div style="width: 3rem; height: 3rem; background-color: #EAF6EF; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: #16845B; flex-shrink: 0;" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.375rem; font-weight: 700; color: #142B4A; margin: 0;">Visi Kami</h3>
                </div>
                <p style="font-size: 1.0625rem; color: #334155; line-height: 1.8; margin: 0;">
                    {{ $about['visi'] ?? '' }}
                </p>
            </div>

            {{-- Kartu Misi --}}
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2.5rem; box-shadow: 0 4px 16px rgba(20,43,74,0.03);">
                <div style="display: flex; align-items: center; gap: 0.875rem; margin-bottom: 1.25rem;">
                    <div style="width: 3rem; height: 3rem; background-color: #EFF6FF; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: #2563EB; flex-shrink: 0;" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 11 12 14 22 4"></polyline>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.375rem; font-weight: 700; color: #142B4A; margin: 0;">Misi Kami</h3>
                </div>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem;">
                    @foreach ($about['misi'] ?? [] as $misi)
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem;">
                            <div style="width: 1.5rem; height: 1.5rem; background-color: #EAF6EF; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;" aria-hidden="true">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <span style="font-size: 0.9375rem; color: #334155; line-height: 1.6;">
                                {{ $misi }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- NILAI-NILAI UTAMA --}}
<section style="padding-top: 5rem; padding-bottom: 5rem; background-color: #ffffff;">
    <div class="container-main">
        <div style="text-align: center; max-width: 600px; margin: 0 auto 3.5rem;">
            <div class="divider-green" style="margin-left: auto; margin-right: auto;"></div>
            <p class="text-section-label" style="margin-bottom: 0.75rem;">Prinsip Kerja</p>
            <h2 class="heading-xl" style="color: #142B4A; margin-bottom: 0.75rem;">Nilai-Nilai Utama Kami</h2>
            <p style="font-size: 1rem; color: #64748b; line-height: 1.6;">Empat pilar nilai yang menjadi standar operasional dan interaksi harian tutor bersama siswa.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
            @foreach ($about['nilai_utama'] ?? [] as $nilai)
                @php
                $color = match($nilai['warna'] ?? 'green') {
                    'blue' => ['bg' => '#EFF6FF', 'stroke' => '#2563EB'],
                    'orange' => ['bg' => '#FFF7ED', 'stroke' => '#EA580C'],
                    'purple' => ['bg' => '#FAF5FF', 'stroke' => '#9333EA'],
                    default => ['bg' => '#EAF6EF', 'stroke' => '#16845B'],
                };
                @endphp
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem 1.75rem; box-shadow: 0 4px 12px rgba(20,43,74,0.03); transition: transform 0.2s ease, box-shadow 0.2s ease;"
                     onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 28px rgba(20,43,74,0.07)';"
                     onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(20,43,74,0.03)';">
                    <div style="width: 3.25rem; height: 3.25rem; background-color: {{ $color['bg'] }}; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="{{ $color['stroke'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            @if (($nilai['ikon'] ?? '') === 'users')
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            @elseif (($nilai['ikon'] ?? '') === 'book-open')
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            @elseif (($nilai['ikon'] ?? '') === 'smile')
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                <line x1="15" y1="9" x2="15.01" y2="9"></line>
                            @else
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            @endif
                        </svg>
                    </div>
                    <h3 style="font-size: 1.125rem; font-weight: 700; color: #142B4A; margin-bottom: 0.5rem;">
                        {{ $nilai['judul'] }}
                    </h3>
                    <p style="font-size: 0.875rem; color: #64748b; line-height: 1.65; margin: 0;">
                        {{ $nilai['deskripsi'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- TIM PENGAJAR / TUTOR UNGGULAN --}}
<section style="padding-top: 5rem; padding-bottom: 5rem; background-color: #F8FAFC; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
    <div class="container-main">
        <div style="text-align: center; max-width: 600px; margin: 0 auto 3.5rem;">
            <div class="divider-green" style="margin-left: auto; margin-right: auto;"></div>
            <p class="text-section-label" style="margin-bottom: 0.75rem;">Tim Pengajar</p>
            <h2 class="heading-xl" style="color: #142B4A; margin-bottom: 0.75rem;">Tutor Berpengalaman &amp; Berdedikasi</h2>
            <p style="font-size: 1rem; color: #64748b; line-height: 1.6;">Lulusan perguruan tinggi unggulan yang tidak hanya menguasai materi, namun tulus mendampingi siswa.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            @foreach ($about['tutor'] ?? [] as $tutor)
                @php
                $avatarColor = match($tutor['warna'] ?? 'blue') {
                    'green' => ['bg' => '#EAF6EF', 'text' => '#16845B'],
                    'orange' => ['bg' => '#FFF7ED', 'text' => '#EA580C'],
                    'purple' => ['bg' => '#FAF5FF', 'text' => '#9333EA'],
                    'red' => ['bg' => '#FEF2F2', 'text' => '#DC2626'],
                    default => ['bg' => '#EFF6FF', 'text' => '#2563EB'],
                };
                @endphp
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.75rem; display: flex; align-items: flex-start; gap: 1.25rem; box-shadow: 0 4px 12px rgba(20,43,74,0.03); transition: transform 0.2s ease, border-color 0.2s ease;"
                     onmouseover="this.style.transform='translateY(-2px)';this.style.borderColor='#16845B';"
                     onmouseout="this.style.transform='translateY(0)';this.style.borderColor='#e2e8f0';">
                    <div style="width: 3.5rem; height: 3.5rem; border-radius: 0.875rem; background-color: {{ $avatarColor['bg'] }}; color: {{ $avatarColor['text'] }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.125rem; flex-shrink: 0;" aria-hidden="true">
                        {{ $tutor['inisial'] ?? 'MB' }}
                    </div>
                    <div>
                        <h3 style="font-size: 1.0625rem; font-weight: 700; color: #142B4A; margin: 0 0 0.25rem 0;">
                            {{ $tutor['nama'] }}
                        </h3>
                        <div style="font-size: 0.8125rem; font-weight: 600; color: #16845B; margin-bottom: 0.35rem;">
                            {{ $tutor['peran'] }}
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.25rem;">
                            🎓 {{ $tutor['lulusan'] }}
                        </div>
                        <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 500;">
                            ⏳ {{ $tutor['pengalaman'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FASILITAS BELAJAR --}}
<section style="padding-top: 5rem; padding-bottom: 5rem; background-color: #ffffff;">
    <div class="container-main">
        <div style="text-align: center; max-width: 600px; margin: 0 auto 3.5rem;">
            <div class="divider-green" style="margin-left: auto; margin-right: auto;"></div>
            <p class="text-section-label" style="margin-bottom: 0.75rem;">Fasilitas</p>
            <h2 class="heading-xl" style="color: #142B4A; margin-bottom: 0.75rem;">Fasilitas Belajar Modern</h2>
            <p style="font-size: 1rem; color: #64748b; line-height: 1.6;">Dukungan sarana pembelajaran yang nyaman dan kondusif untuk mendukung konsentrasi.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
            @foreach ($about['fasilitas'] ?? [] as $fasilitas)
                <div style="background-color: #F8FAFC; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem 1.75rem;">
                    <div style="width: 3rem; height: 3rem; background-color: #EAF6EF; border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; color: #16845B; margin-bottom: 1.25rem;" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            @if (($fasilitas['ikon'] ?? '') === 'book')
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            @elseif (($fasilitas['ikon'] ?? '') === 'laptop')
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="2" y1="20" x2="22" y2="20"></line>
                            @elseif (($fasilitas['ikon'] ?? '') === 'chat')
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            @else
                                <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                <line x1="9" y1="22" x2="9" y2="2"></line>
                                <line x1="8" y1="6" x2="8.01" y2="6"></line>
                                <line x1="16" y1="6" x2="16.01" y2="6"></line>
                                <line x1="12" y1="6" x2="12.01" y2="6"></line>
                            @endif
                        </svg>
                    </div>
                    <h3 style="font-size: 1.125rem; font-weight: 700; color: #142B4A; margin-bottom: 0.5rem;">
                        {{ $fasilitas['nama'] }}
                    </h3>
                    <p style="font-size: 0.875rem; color: #64748b; line-height: 1.6; margin: 0;">
                        {{ $fasilitas['deskripsi'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA PENUTUP --}}
<section style="padding-top: 5rem; padding-bottom: 5rem; background: linear-gradient(135deg, #142B4A 0%, #0e1f36 100%); color: #ffffff;">
    <div class="container-main" style="text-align: center; max-width: 680px;">
        <span class="badge-pill" style="background-color: rgba(22,132,91,0.25); color: #86efac; border: 1px solid rgba(134,239,172,0.3); font-size: 0.75rem; margin-bottom: 1.25rem;">
            Bergabung Bersama Kami
        </span>
        <h2 style="font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 800; color: #ffffff; margin-bottom: 1rem; line-height: 1.2;">
            Raih Potensi Akademik Terbaik Bersama Modern Brilliant
        </h2>
        <p style="font-size: 1.0625rem; color: rgba(255,255,255,0.8); line-height: 1.75; margin-bottom: 2.25rem;">
            Daftarkan putra-putri Anda sekarang atau konsultasikan kebutuhan belajarnya bersama konselor pendidikan kami secara gratis.
        </p>
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem;">
            <a href="{{ route('program.index') }}" class="btn-primary" style="background-color: #16845B; border-color: #16845B;">
                Jelajahi Program Belajar
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            <a href="{{ url('/#contact') }}" class="btn-outline-white">
                Konsultasi Gratis
            </a>
        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    function updateAboutGrids() {
        var storyGrid = document.getElementById('about-story-grid');
        var visionGrid = document.getElementById('vision-mission-grid');
        var w = window.innerWidth;
        if (storyGrid) {
            storyGrid.style.gridTemplateColumns = w >= 1024 ? '1.2fr 1fr' : '1fr';
        }
        if (visionGrid) {
            visionGrid.style.gridTemplateColumns = w >= 768 ? '1fr 1fr' : '1fr';
        }
    }
    window.addEventListener('resize', updateAboutGrids);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateAboutGrids);
    } else {
        updateAboutGrids();
    }
})();
</script>
@endpush
@endsection
