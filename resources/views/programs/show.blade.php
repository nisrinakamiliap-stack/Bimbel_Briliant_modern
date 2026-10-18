@extends('layouts.app')

@section('title', $program['judul'] . ' — Bimbel Sentra Edukasi')
@section('meta_description', Str::limit(strip_tags($program['deskripsi_panjang'] ?? $program['deskripsi']), 160))

@section('content')
{{-- HEADER / BREADCRUMB SECTION --}}
<section style="background-color: #ffffff; padding-top: 2rem; padding-bottom: 2.5rem; border-bottom: 1px solid #e2e8f0;">
    <div class="container-main">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" style="margin-bottom: 1.5rem;">
            <ol style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.875rem;">
                <li>
                    <a href="{{ route('home') }}" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#16845B'" onmouseout="this.style.color='#64748b'">Beranda</a>
                </li>
                <li style="color: #cbd5e1;" aria-hidden="true">/</li>
                <li>
                    <a href="{{ route('program.index') }}" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#16845B'" onmouseout="this.style.color='#64748b'">Program Belajar</a>
                </li>
                <li style="color: #cbd5e1;" aria-hidden="true">/</li>
                <li style="color: #142B4A; font-weight: 600;" aria-current="page">{{ $program['judul'] }}</li>
            </ol>
        </nav>

        {{-- Program Overview Card / Hero --}}
        <div style="max-width: 860px;">
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.625rem; margin-bottom: 1rem;">
                @if (!empty($program['kelas']))
                    <span class="badge-pill {{ $program['badge_class'] ?? 'badge-green' }}" style="font-size: 0.8125rem; padding: 0.35rem 0.875rem;">
                        {{ $program['kelas'] }}
                    </span>
                @endif
                @if (!empty($program['populer']))
                    <span class="badge-pill badge-green" style="font-size: 0.8125rem; padding: 0.35rem 0.875rem; border: 1px solid rgba(22, 132, 91, 0.3); font-weight: 700;">
                        ⭐ Program Populer
                    </span>
                @endif
            </div>

            <h1 class="heading-xl" style="color: #142B4A; margin-bottom: 1.25rem;">
                {{ $program['judul'] }}
            </h1>

            <p style="font-size: 1.0625rem; color: #4a5568; line-height: 1.8; margin-bottom: 2rem;">
                {{ $program['deskripsi_panjang'] ?? $program['deskripsi'] }}
            </p>

            {{-- Action Buttons --}}
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                <a href="{{ url('/#contact') }}" class="btn-primary" id="btn-daftar-detail" style="border-radius: 0.5rem;">
                    Daftar Sekarang
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <a href="{{ url('/#contact') }}" class="btn-secondary" id="btn-konsultasi-detail" style="border-radius: 0.5rem;">
                    Konsultasi Gratis
                </a>
                <a href="{{ route('program.index') }}" style="display: inline-flex; align-items: center; gap: 0.375rem; color: #64748b; font-size: 0.875rem; font-weight: 500; text-decoration: none; margin-left: 0.5rem; transition: color 0.2s;" onmouseover="this.style.color='#16845B'" onmouseout="this.style.color='#64748b'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Semua Program
                </a>
            </div>
        </div>
    </div>
</section>

{{-- DETAIL CONTENT & SIDEBAR SECTION --}}
<section style="padding-top: 3.5rem; padding-bottom: 5rem; background-color: #F8FAFC;">
    <div class="container-main">
        <div id="detail-layout" style="display: grid; grid-template-columns: 1fr; gap: 2.5rem; align-items: start;">

            {{-- MAIN COLUMN --}}
            <div style="display: flex; flex-direction: column; gap: 2rem;">

                {{-- Card 1: Materi yang Diajarkan --}}
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem 2.25rem; box-shadow: 0 4px 16px rgba(20,43,74,0.04);">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                        <div style="width: 3rem; height: 3rem; background-color: #EAF6EF; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #142B4A; margin: 0 0 0.25rem 0;">Materi yang Diajarkan</h2>
                            <p style="font-size: 0.875rem; color: #64748b; margin: 0;">Kurikulum dirancang adaptif mengikuti standar kompetensi pendidikan nasional</p>
                        </div>
                    </div>

                    @if (!empty($program['materi']))
                        <div style="display: flex; flex-direction: column; gap: 0.875rem;">
                            @foreach ($program['materi'] as $index => $item)
                                <div style="display: flex; align-items: flex-start; gap: 0.875rem; padding: 0.75rem 1rem; background-color: #f8fafc; border-radius: 0.625rem; border: 1px solid #f1f5f9;">
                                    <div style="width: 1.5rem; height: 1.5rem; background-color: #EAF6EF; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;" aria-hidden="true">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </div>
                                    <span style="font-size: 0.9375rem; color: #334155; line-height: 1.6;">
                                        {{ $item }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="font-size: 0.9375rem; color: #64748b;">Materi lengkap dapat dikonsultasikan saat pendaftaran.</p>
                    @endif
                </div>

                {{-- Card 2: Jadwal Belajar --}}
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem 2.25rem; box-shadow: 0 4px 16px rgba(20,43,74,0.04);">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                        <div style="width: 3rem; height: 3rem; background-color: #EAF6EF; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <div>
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #142B4A; margin: 0 0 0.25rem 0;">Jadwal Belajar &amp; Sesi</h2>
                            <p style="font-size: 0.875rem; color: #64748b; margin: 0;">Pilihan waktu yang fleksibel disesuaikan dengan agenda sekolah siswa</p>
                        </div>
                    </div>

                    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.75rem; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.625rem; font-weight: 700; color: #166534; font-size: 0.9375rem; margin-bottom: 0.375rem;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            Jadwal Reguler:
                        </div>
                        <p style="font-size: 0.9375rem; color: #15803d; margin: 0; line-height: 1.6;">
                            {{ $program['jadwal'] ?? '3x seminggu (Senin, Rabu, Jumat atau Selasa, Kamis, Sabtu)' }}
                        </p>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                        <div style="padding: 1rem; border: 1px solid #e2e8f0; border-radius: 0.625rem;">
                            <div style="font-size: 0.8125rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Rasio Siswa</div>
                            <div style="font-size: 1rem; font-weight: 700; color: #142B4A;">{{ $program['rasio'] ?? '5-8 Siswa / Kelas' }}</div>
                        </div>
                        <div style="padding: 1rem; border: 1px solid #e2e8f0; border-radius: 0.625rem;">
                            <div style="font-size: 0.8125rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Konsultasi Tugas</div>
                            <div style="font-size: 1rem; font-weight: 700; color: #142B4A;">Tersedia di luar jam kelas</div>
                        </div>
                        <div style="padding: 1rem; border: 1px solid #e2e8f0; border-radius: 0.625rem;">
                            <div style="font-size: 0.8125rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Fleksibilitas</div>
                            <div style="font-size: 1rem; font-weight: 700; color: #142B4A;">Bisa ganti sesi dengan izin</div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Manfaat & Fasilitas --}}
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem 2.25rem; box-shadow: 0 4px 16px rgba(20,43,74,0.04);">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                        <div style="width: 3rem; height: 3rem; background-color: #EAF6EF; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #142B4A; margin: 0 0 0.25rem 0;">Manfaat &amp; Keunggulan</h2>
                            <p style="font-size: 0.875rem; color: #64748b; margin: 0;">Nilai lebih yang didapatkan siswa selama mengikuti program bimbingan</p>
                        </div>
                    </div>

                    @if (!empty($program['manfaat']))
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                            @foreach ($program['manfaat'] as $manfaat)
                                <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 1rem; background-color: #f8fafc; border-radius: 0.625rem; border: 1px solid #e2e8f0;">
                                    <div style="width: 1.5rem; height: 1.5rem; background-color: #EAF6EF; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;" aria-hidden="true">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </div>
                                    <span style="font-size: 0.875rem; color: #334155; line-height: 1.6;">
                                        {{ $manfaat }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- SIDEBAR STICKY SUMMARY COLUMN --}}
            <aside style="position: sticky; top: 6rem;">
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem; box-shadow: 0 12px 32px rgba(20,43,74,0.06);">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #16845B; margin-bottom: 0.5rem;">
                        Ringkasan Program
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #142B4A; margin-bottom: 1.5rem;">
                        {{ $program['judul'] }}
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem;">
                            <span style="color: #64748b;">Target Siswa:</span>
                            <span style="font-weight: 600; color: #142B4A;">{{ $program['kelas'] ?? 'Semua Kelas' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem;">
                            <span style="color: #64748b;">Kapasitas:</span>
                            <span style="font-weight: 600; color: #142B4A;">{{ $program['rasio'] ?? '5-8 Siswa' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem;">
                            <span style="color: #64748b;">Investasi:</span>
                            <span style="font-weight: 700; color: #16845B;">{{ $program['biaya'] ?? 'Hubungi Admin' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem;">
                            <span style="color: #64748b;">Status:</span>
                            <span class="badge-pill badge-green" style="font-size: 0.7rem;">Pendaftaran Dibuka</span>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="{{ url('/#contact') }}" class="btn-primary" style="justify-content: center; width: 100%;">
                            Daftar Sekarang
                        </a>
                        <a href="{{ url('/#contact') }}" class="btn-secondary" style="justify-content: center; width: 100%;">
                            Konsultasi Gratis
                        </a>
                    </div>

                    <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9; text-align: center;">
                        <p style="font-size: 0.75rem; color: #94a3b8; margin: 0; line-height: 1.5;">
                            Butuh bantuan lebih cepat? Hubungi call center kami di <strong>(021) 7890-1234</strong>
                        </p>
                    </div>
                </div>
            </aside>

        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    function updateDetailLayout() {
        var layout = document.getElementById('detail-layout');
        if (!layout) return;
        if (window.innerWidth >= 1024) {
            layout.style.gridTemplateColumns = '2fr 1fr';
        } else {
            layout.style.gridTemplateColumns = '1fr';
        }
    }
    window.addEventListener('resize', updateDetailLayout);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateDetailLayout);
    } else {
        updateDetailLayout();
    }
})();
</script>
@endpush
@endsection
