@props(['jadwal'])

@php
$color = $jadwal['warna_ikon'] ?? 'blue';
$colorStyles = match($color) {
    'green' => ['bg' => '#EAF6EF', 'stroke' => '#16845B', 'badge_bg' => '#EAF6EF', 'badge_text' => '#16845B'],
    'orange' => ['bg' => '#FFF7ED', 'stroke' => '#EA580C', 'badge_bg' => '#FFEDD5', 'badge_text' => '#C2410C'],
    'red' => ['bg' => '#FEF2F2', 'stroke' => '#DC2626', 'badge_bg' => '#FEE2E2', 'badge_text' => '#B91C1C'],
    'purple' => ['bg' => '#FAF5FF', 'stroke' => '#9333EA', 'badge_bg' => '#F3E8FF', 'badge_text' => '#7E22CE'],
    default => ['bg' => '#EFF6FF', 'stroke' => '#2563EB', 'badge_bg' => '#DBEAFE', 'badge_text' => '#1D4ED8'],
};

$slug = $jadwal['program_slug'] ?? 'sd';
$mapelLower = strtolower($jadwal['mapel'] ?? '');
@endphp

<a href="{{ route('program.show', $slug) }}"
   class="jadwal-item-card"
   style="display: block; text-decoration: none; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.125rem 1.375rem; margin-bottom: 0.875rem; box-shadow: 0 2px 8px rgba(20,43,74,0.03); transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; cursor: pointer;"
   onmouseover="this.style.borderColor='#16845B';this.style.boxShadow='0 10px 24px rgba(20,43,74,0.08)';this.style.transform='translateY(-2px)';"
   onmouseout="this.style.borderColor='#e2e8f0';this.style.boxShadow='0 2px 8px rgba(20,43,74,0.03)';this.style.transform='translateY(0)';"
   aria-label="{{ $jadwal['mapel'] }} - {{ $jadwal['kelas'] }} ({{ $jadwal['jam_mulai'] }} - {{ $jadwal['jam_selesai'] }})">

    <div class="jadwal-row" style="display: flex; align-items: center; justify-content: space-between; gap: 1.25rem;">

        {{-- Sisi Kiri: Tanggal + Ikon + Info Mapel --}}
        <div style="display: flex; align-items: center; gap: 1rem; min-width: 0; flex: 1;">

            {{-- Kolom Hari & Tanggal Singkat --}}
            <div style="width: 3.5rem; height: 3.5rem; background-color: #F8FAFC; border: 1px solid #e2e8f0; border-radius: 0.625rem; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; text-align: center;">
                <span style="font-size: 0.875rem; font-weight: 800; color: #142B4A; line-height: 1.1; text-transform: uppercase;">
                    {{ $jadwal['nama_hari_singkat'] ?? 'Sen' }}
                </span>
                <span style="font-size: 0.6875rem; font-weight: 600; color: #64748b; line-height: 1.1; margin-top: 3px;">
                    {{ $jadwal['tanggal_singkat'] ?? '' }}
                </span>
            </div>

            {{-- Ikon Mapel di Kotak Berwarna --}}
            <div style="width: 3rem; height: 3rem; background-color: {{ $colorStyles['bg'] }}; border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="{{ $colorStyles['stroke'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    @if (str_contains($mapelLower, 'matematika') || str_contains($mapelLower, 'kuantitatif') || str_contains($mapelLower, 'hitung'))
                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                        <line x1="8" y1="6" x2="16" y2="6"></line>
                        <line x1="16" y1="14" x2="16" y2="18"></line>
                        <path d="M16 10h.01"></path>
                        <path d="M12 10h.01"></path>
                        <path d="M8 10h.01"></path>
                        <path d="M12 14h.01"></path>
                        <path d="M8 14h.01"></path>
                        <path d="M12 18h.01"></path>
                        <path d="M8 18h.01"></path>
                    @elseif (str_contains($mapelLower, 'fisika') || str_contains($mapelLower, 'ipa') || str_contains($mapelLower, 'sains'))
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"></path>
                    @elseif (str_contains($mapelLower, 'kimia'))
                        <path d="M10 2v7.31"></path>
                        <path d="M14 2v7.31"></path>
                        <path d="M8.5 2h7"></path>
                        <path d="M14 9.3 18.9 18H5.1L10 9.3"></path>
                        <path d="M7 16h10"></path>
                    @elseif (str_contains($mapelLower, 'biologi'))
                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path>
                        <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>
                    @elseif (str_contains($mapelLower, 'inggris') || str_contains($mapelLower, 'bahasa') || str_contains($mapelLower, 'literasi'))
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    @elseif (str_contains($mapelLower, 'utbk') || str_contains($mapelLower, 'ujian') || str_contains($mapelLower, 'penalaran'))
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    @else
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    @endif
                </svg>
            </div>

            {{-- Nama Mapel, Kelas & Tipe, Nama Pengajar --}}
            <div style="min-width: 0;">
                <h3 style="font-size: 1.0625rem; font-weight: 700; color: #142B4A; margin: 0 0 0.35rem 0; line-height: 1.3;">
                    {{ $jadwal['mapel'] }}
                </h3>
                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    {{-- Kelas dan tipe --}}
                    <span style="display: inline-flex; align-items: center; font-size: 0.75rem; font-weight: 600; background-color: {{ $colorStyles['badge_bg'] }}; color: {{ $colorStyles['badge_text'] }}; padding: 0.15rem 0.625rem; border-radius: 999px;">
                        {{ $jadwal['kelas'] }} - {{ $jadwal['tipe'] }}
                    </span>

                    {{-- Nama Pengajar dengan ikon orang --}}
                    <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.8125rem; color: #64748b; font-weight: 500;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        {{ $jadwal['pengajar'] }}
                    </span>
                </div>
            </div>

        </div>

        {{-- Sisi Kanan: Jam Mulai & Selesai + Chevron Kanan --}}
        <div class="jadwal-right-col" style="display: flex; align-items: center; gap: 1rem; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 0.45rem; background-color: #F8FAFC; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 0.4rem 0.875rem; font-size: 0.875rem; font-weight: 600; color: #142B4A;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>{{ $jadwal['jam_mulai'] }} - {{ $jadwal['jam_selesai'] }}</span>
            </div>

            <div class="jadwal-chevron" style="width: 2rem; height: 2rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #94a3b8;" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </div>

    </div>
</a>
