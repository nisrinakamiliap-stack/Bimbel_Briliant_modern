@extends('layouts.app')

@section('title', 'Kontak & Konsultasi Belajar — Bimbel Sentra Edukasi')
@section('meta_description', 'Hubungi Bimbel Sentra Edukasi untuk konsultasi program belajar gratis, informasi jadwal kelas, biaya bimbingan, dan pendaftaran siswa baru.')

@section('content')
{{-- HEADER / BREADCRUMB SECTION --}}
<section style="background-color: #ffffff; padding-top: 2.5rem; padding-bottom: 3.5rem; border-bottom: 1px solid #f1f5f9;">
    <div class="container-main">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" style="margin-bottom: 1.5rem;">
            <ol style="display: flex; align-items: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.875rem;">
                <li>
                    <a href="{{ route('home') }}" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#16845B'" onmouseout="this.style.color='#64748b'">Beranda</a>
                </li>
                <li style="color: #cbd5e1;" aria-hidden="true">/</li>
                <li style="color: #142B4A; font-weight: 600;" aria-current="page">Kontak</li>
            </ol>
        </nav>

        {{-- Header Title & Subtitle --}}
        <div style="text-align: center; max-width: 760px; margin: 0 auto;">
            <div class="divider-green" style="margin-left: auto; margin-right: auto;"></div>
            <p class="text-section-label" style="margin-bottom: 0.75rem;">Layanan Konsultasi</p>
            <h1 class="heading-xl" style="margin-bottom: 1rem; color: #142B4A;">
                Kami Siap Membantu Perjalanan Belajarmu
            </h1>
            <p style="font-size: 1.0625rem; color: #4a5568; line-height: 1.75; margin: 0 auto;">
                Punya pertanyaan seputar program belajar, jadwal kelas, atau ingin konsultasi pendidikan gratis? Tim konselor Bimbel Sentra Edukasi siap melayani Anda.
            </p>
        </div>
    </div>
</section>

{{-- KONTEN UTAMA: INFORMASI KONTAK & FORMULIR --}}
<section style="padding-top: 3.5rem; padding-bottom: 5rem; background-color: #F8FAFC;">
    <div class="container-main">

        {{-- Flash Success Alert --}}
        @if (session('success'))
            <div style="background-color: #f0fdf4; border: 1px solid #86efac; border-radius: 0.875rem; padding: 1.25rem 1.5rem; margin-bottom: 2.5rem; display: flex; align-items: flex-start; gap: 1rem; box-shadow: 0 4px 12px rgba(22,132,91,0.08);">
                <div style="width: 2rem; height: 2rem; border-radius: 50%; background-color: #dcfce7; display: flex; align-items: center; justify-content: center; color: #16845B; flex-shrink: 0; margin-top: 2px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div style="flex: 1;">
                    <h2 style="font-size: 1rem; font-weight: 700; color: #166534; margin: 0 0 0.25rem 0;">Pesan Terkirim Berhasil!</h2>
                    <p style="font-size: 0.9375rem; color: #15803d; margin: 0; line-height: 1.6;">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <div style="display: grid; grid-template-columns: 1fr; gap: 3rem; align-items: start;" id="contact-main-grid">

            {{-- KOLOM KIRI: KARTU KONTAK & INFO CABANG --}}
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                {{-- Highlight WhatsApp --}}
                <div style="background: linear-gradient(135deg, #142B4A 0%, #1d3d6b 100%); border-radius: 1rem; padding: 2rem; color: #ffffff; box-shadow: 0 12px 32px rgba(20,43,74,0.1);">
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; background-color: rgba(22,132,91,0.25); color: #86efac; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.25rem 0.75rem; border-radius: 999px; margin-bottom: 1rem;">
                        ⚡ Respon Tercepat
                    </div>
                    <h2 style="font-size: 1.375rem; font-weight: 700; color: #ffffff; margin-bottom: 0.5rem;">
                        WhatsApp Konsultasi Belajar
                    </h2>
                    <p style="font-size: 0.9375rem; color: rgba(255,255,255,0.8); line-height: 1.6; margin-bottom: 1.5rem;">
                        Hubungi konselor akademik kami langsung melalui pesan WhatsApp untuk informasi pendaftaran kilat dan rekomendasi kelas.
                    </p>
                    <a href="{{ $kontak['whatsapp_link'] ?? 'https://wa.me/6281234567890' }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn-primary"
                       style="background-color: #16845B; border-color: #16845B; width: 100%; justify-content: center; border-radius: 0.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        Chat via WhatsApp ({{ $kontak['whatsapp'] ?? '0812-3456-7890' }})
                    </a>
                </div>

                {{-- Detail Kontak Kartu --}}
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 16px rgba(20,43,74,0.03);">
                    <h3 style="font-size: 1.125rem; font-weight: 700; color: #142B4A; margin-bottom: 1.5rem; padding-bottom: 0.875rem; border-bottom: 1px solid #f1f5f9;">
                        Saluran Komunikasi Resmi
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 1.375rem;">
                        {{-- Telepon --}}
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.625rem; background-color: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 0.25rem;">Call Center Kantor</div>
                                <div style="font-size: 1rem; font-weight: 700; color: #142B4A;">{{ $kontak['telepon'] ?? '(021) 7890-1234' }}</div>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.625rem; background-color: #FFF7ED; color: #EA580C; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 0.25rem;">Email Resmi</div>
                                <div style="font-size: 1rem; font-weight: 700; color: #142B4A;">{{ $kontak['email'] ?? 'info@modernbrilliant.sch.id' }}</div>
                            </div>
                        </div>

                        {{-- Alamat --}}
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.625rem; background-color: #EAF6EF; color: #16845B; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 0.25rem;">Kantor Pusat</div>
                                <div style="font-size: 0.9375rem; color: #334155; line-height: 1.6;">{{ $kontak['alamat'] ?? 'Jl. Pendidikan Utama No. 88, Kebayoran Baru, Jakarta Selatan — 12340' }}</div>
                            </div>
                        </div>

                        {{-- Jam Kerja --}}
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.625rem; background-color: #FAF5FF; color: #9333EA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 0.25rem;">Jam Operasional Kantor</div>
                                <div style="font-size: 0.9375rem; color: #334155; line-height: 1.6;">{{ $kontak['jam_operasional'] ?? 'Senin – Sabtu, 08.00 – 20.00 WIB' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Visual Peta Lokasi / Map Card --}}
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 16px rgba(20,43,74,0.03);">
                    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 0.9375rem; font-weight: 700; color: #142B4A;">Peta Lokasi Bimbel</span>
                        <span class="badge-pill badge-green" style="font-size: 0.7rem;">Kebayoran Baru, Jakarta</span>
                    </div>
                    <div style="aspect-ratio: 16/9; width: 100%; background-color: #f1f5f9; position: relative;">
                        <iframe
                            src="{{ $kontak['maps_embed'] ?? 'https://maps.google.com/maps?q=Jakarta+Selatan&t=&z=13&ie=UTF8&iwloc=&output=embed' }}"
                            style="width: 100%; height: 100%; border: 0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Peta Lokasi Bimbel Sentra Edukasi">
                        </iframe>
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN: FORMULIR KONSULTASI / PESAN --}}
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 2.5rem; box-shadow: 0 8px 30px rgba(20,43,74,0.05);">
                <div style="margin-bottom: 2rem;">
                    <span class="badge-pill badge-green" style="font-size: 0.75rem; margin-bottom: 0.75rem;">
                        Formulir Bebas Biaya
                    </span>
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: #142B4A; margin-bottom: 0.5rem;">
                        Ajukan Konsultasi / Kirim Pesan
                    </h2>
                    <p style="font-size: 0.9375rem; color: #64748b; line-height: 1.6; margin: 0;">
                        Lengkapi informasi di bawah ini. Konselor akademik kami akan menghubungi Anda untuk menjawab kebutuhan belajar siswa.
                    </p>
                </div>

                <form action="{{ route('kontak.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.375rem;" id="form-kontak">
                    @csrf

                    {{-- Nama Lengkap --}}
                    <div>
                        <label for="nama" style="display: block; font-size: 0.875rem; font-weight: 600; color: #142B4A; margin-bottom: 0.375rem;">
                            Nama Lengkap <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text"
                               name="nama"
                               id="nama"
                               value="{{ old('nama') }}"
                               placeholder="Contoh: Budi Santoso"
                               required
                               style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid {{ $errors->has('nama') ? '#dc2626' : '#cbd5e1' }}; font-size: 0.9375rem; outline: none; transition: border-color 0.2s;"
                               onfocus="this.style.borderColor='#16845B';"
                               onblur="this.style.borderColor='{{ $errors->has('nama') ? '#dc2626' : '#cbd5e1' }}';">
                        @error('nama')
                            <p style="font-size: 0.8125rem; color: #dc2626; margin: 0.375rem 0 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Telepon & Email (2 kolom di tablet/desktop) --}}
                    <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;" id="form-contact-row">
                        {{-- WhatsApp / No Telepon --}}
                        <div>
                            <label for="telepon" style="display: block; font-size: 0.875rem; font-weight: 600; color: #142B4A; margin-bottom: 0.375rem;">
                                No. WhatsApp / Telepon <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="tel"
                                   name="telepon"
                                   id="telepon"
                                   value="{{ old('telepon') }}"
                                   placeholder="Contoh: 0812-3456-7890"
                                   required
                                   style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid {{ $errors->has('telepon') ? '#dc2626' : '#cbd5e1' }}; font-size: 0.9375rem; outline: none; transition: border-color 0.2s;"
                                   onfocus="this.style.borderColor='#16845B';"
                                   onblur="this.style.borderColor='{{ $errors->has('telepon') ? '#dc2626' : '#cbd5e1' }}';">
                            @error('telepon')
                                <p style="font-size: 0.8125rem; color: #dc2626; margin: 0.375rem 0 0 0;">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" style="display: block; font-size: 0.875rem; font-weight: 600; color: #142B4A; margin-bottom: 0.375rem;">
                                Alamat Email <span style="font-size: 0.75rem; color: #94a3b8; font-weight: normal;">(Opsional)</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email') }}"
                                   placeholder="nama@email.com"
                                   style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid {{ $errors->has('email') ? '#dc2626' : '#cbd5e1' }}; font-size: 0.9375rem; outline: none; transition: border-color 0.2s;"
                                   onfocus="this.style.borderColor='#16845B';"
                                   onblur="this.style.borderColor='{{ $errors->has('email') ? '#dc2626' : '#cbd5e1' }}';">
                            @error('email')
                                <p style="font-size: 0.8125rem; color: #dc2626; margin: 0.375rem 0 0 0;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Pilihan Jenjang / Program --}}
                    <div>
                        <label for="jenjang" style="display: block; font-size: 0.875rem; font-weight: 600; color: #142B4A; margin-bottom: 0.375rem;">
                            Program Belajar yang Diminati <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="jenjang"
                                id="jenjang"
                                required
                                style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid {{ $errors->has('jenjang') ? '#dc2626' : '#cbd5e1' }}; font-size: 0.9375rem; outline: none; background-color: #ffffff; cursor: pointer;">
                            <option value="">-- Pilih Program Belajar --</option>
                            <option value="SD" {{ old('jenjang') === 'SD' ? 'selected' : '' }}>Bimbingan Belajar SD (Kelas 1–6)</option>
                            <option value="SMP" {{ old('jenjang') === 'SMP' ? 'selected' : '' }}>Bimbingan Belajar SMP (Kelas 7–9)</option>
                            <option value="SMA" {{ old('jenjang') === 'SMA' ? 'selected' : '' }}>Bimbingan Belajar SMA (Kelas 10–12)</option>
                            <option value="Persiapan Ujian / UTBK" {{ old('jenjang') === 'Persiapan Ujian / UTBK' ? 'selected' : '' }}>Persiapan Ujian &amp; SNBT UTBK</option>
                            <option value="Privat 1-on-1" {{ old('jenjang') === 'Privat 1-on-1' ? 'selected' : '' }}>Kelas Privat Fleksibel (1-on-1)</option>
                            <option value="Lainnya" {{ old('jenjang') === 'Lainnya' ? 'selected' : '' }}>Konsultasi Umum / Lainnya</option>
                        </select>
                        @error('jenjang')
                            <p style="font-size: 0.8125rem; color: #dc2626; margin: 0.375rem 0 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pesan / Pertanyaan --}}
                    <div>
                        <label for="pesan" style="display: block; font-size: 0.875rem; font-weight: 600; color: #142B4A; margin-bottom: 0.375rem;">
                            Pesan / Pertanyaan Anda <span style="color: #dc2626;">*</span>
                        </label>
                        <textarea name="pesan"
                                  id="pesan"
                                  rows="4"
                                  placeholder="Tuliskan pertanyaan seputar target belajar, kendala mapel, atau preferensi jadwal yang Anda inginkan..."
                                  required
                                  style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid {{ $errors->has('pesan') ? '#dc2626' : '#cbd5e1' }}; font-size: 0.9375rem; outline: none; transition: border-color 0.2s; resize: vertical;"
                                  onfocus="this.style.borderColor='#16845B';"
                                  onblur="this.style.borderColor='{{ $errors->has('pesan') ? '#dc2626' : '#cbd5e1' }}';">{{ old('pesan') }}</textarea>
                        @error('pesan')
                            <p style="font-size: 0.8125rem; color: #dc2626; margin: 0.375rem 0 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Submit --}}
                    <button type="submit"
                            class="btn-primary"
                            id="btn-submit-kontak"
                            style="width: 100%; justify-content: center; padding: 0.875rem 1.5rem; font-size: 1rem; border-radius: 0.5rem;">
                        Kirim Pesan / Ajukan Konsultasi
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>

                    <p style="font-size: 0.75rem; color: #94a3b8; text-align: center; margin: 0; line-height: 1.5;">
                        🔒 Privasi Anda terjaga. Kami tidak akan menyebarkan informasi kontak Anda kepada pihak mana pun.
                    </p>
                </form>
            </div>

        </div>

        {{-- SECTION FAQ --}}
        <div style="margin-top: 5rem;">
            <div style="text-align: center; max-width: 600px; margin: 0 auto 3rem;">
                <div class="divider-green" style="margin-left: auto; margin-right: auto;"></div>
                <p class="text-section-label" style="margin-bottom: 0.75rem;">Pertanyaan Umum</p>
                <h2 class="heading-lg" style="color: #142B4A; margin-bottom: 0.5rem;">Pertanyaan yang Sering Diajukan</h2>
                <p style="font-size: 0.9375rem; color: #64748b; line-height: 1.6;">Jawaban cepat untuk hal-hal yang sering ditanyakan orang tua siswa.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                @foreach ($kontak['faq'] ?? [] as $index => $item)
                    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.75rem; box-shadow: 0 4px 12px rgba(20,43,74,0.03);">
                        <div style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div style="width: 1.75rem; height: 1.75rem; border-radius: 50%; background-color: #EAF6EF; color: #16845B; display: flex; align-items: center; justify-content: center; font-size: 0.8125rem; font-weight: 700; flex-shrink: 0; margin-top: 2px;">
                                Q{{ $index + 1 }}
                            </div>
                            <h3 style="font-size: 1rem; font-weight: 700; color: #142B4A; line-height: 1.4; margin: 0;">
                                {{ $item['tanya'] }}
                            </h3>
                        </div>
                        <p style="font-size: 0.875rem; color: #64748b; line-height: 1.65; margin: 0; padding-left: 2.5rem;">
                            {{ $item['jawab'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</section>

@push('scripts')
<script>
(function () {
    function updateContactLayout() {
        var grid = document.getElementById('contact-main-grid');
        var formRow = document.getElementById('form-contact-row');
        var w = window.innerWidth;
        if (grid) {
            grid.style.gridTemplateColumns = w >= 1024 ? '1fr 1.25fr' : '1fr';
        }
        if (formRow) {
            formRow.style.gridTemplateColumns = w >= 640 ? '1fr 1fr' : '1fr';
        }
    }
    window.addEventListener('resize', updateContactLayout);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateContactLayout);
    } else {
        updateContactLayout();
    }
})();
</script>
@endpush
@endsection
