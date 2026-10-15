<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Data Program Belajar Bimbel Modern Brilliant
    |--------------------------------------------------------------------------
    |
    | Seluruh data program bimbingan belajar disimpan secara terstruktur di sini.
    | Jangan menulis data program langsung di dalam view Blade.
    |
    */

    'programs' => [
        'sd' => [
            'slug' => 'sd',
            'judul' => 'Bimbingan Belajar SD',
            'kelas' => 'Kelas 1-6',
            'badge_class' => 'badge-green',
            'deskripsi' => 'Membangun fondasi belajar yang kuat dengan pendekatan yang menyenangkan dan sesuai usia. Fokus pada Matematika, Bahasa Indonesia, IPA, dan IPS.',
            'deskripsi_panjang' => 'Program Bimbingan Belajar SD di Modern Brilliant dirancang khusus untuk membangun fondasi akademik yang kokoh dan menumbuhkan rasa percaya diri siswa sejak dini. Melalui pendekatan active and fun learning, tutor membimbing siswa memahami konsep secara mendalam tanpa rasa jenuh. Kami mendampingi pemahaman materi harian, penyelesaian PR, serta persiapan Penilaian Harian dan Asesmen Akhir Semester.',
            'ikon' => 'book',
            'populer' => false,
            'poin' => [
                'Target: Siswa SD kelas 1-6',
                'Sesi kelompok kecil & individual',
                'Pendampingan PR dan tugas sekolah setiap hari',
                'Penguatan konsep dasar berhitung, logika, dan literasi',
            ],
            'materi' => [
                'Matematika: Operasi hitung dasar, pecahan, bangun datar & ruang, pemecahan masalah (soal cerita kontekstual)',
                'Bahasa Indonesia: Membaca pemahaman, pengayaan kosakata, tata bahasa dasar, dan keterampilan menulis karangan',
                'Ilmu Pengetahuan Alam (IPA): Pengenalan lingkungan, makhluk hidup, energi, dan eksperimen sains interaktif',
                'Ilmu Pengetahuan Sosial (IPS): Pengenalan kehidupan sosial, sejarah kebangsaan, dan keberagaman budaya',
                'Persiapan berkala menghadapi Penilaian Harian (PH), Sumatif Tengah Semester, dan Penilaian Akhir Semester (PAS)',
            ],
            'jadwal' => 'Pilihan hari: Senin, Rabu, Jumat atau Selasa, Kamis, Sabtu • Pukul 15.30 - 17.00 WIB (Durasi: 90 menit per sesi)',
            'manfaat' => [
                'Fondasi konsep matematika dan sains yang kokoh sebelum memasuki jenjang SMP',
                'Metode interaktif membuat anak lebih antusias dan mandiri dalam belajar',
                'Tutor yang sabar, teliti, dan menguasai psikologi perkembangan anak',
                'Laporan perkembangan belajar tertulis secara berkala untuk orang tua setiap bulan',
            ],
            'biaya' => 'Mulai dari Rp350.000 / bulan',
            'rasio' => 'Maksimal 5-6 siswa per kelompok',
        ],

        'smp' => [
            'slug' => 'smp',
            'judul' => 'Bimbingan Belajar SMP',
            'kelas' => 'Kelas 7-9',
            'badge_class' => 'badge-navy',
            'deskripsi' => 'Membantu siswa SMP menguasai mata pelajaran inti dengan metode belajar yang lebih terstruktur. Persiapan ujian akhir semester dan ujian sekolah.',
            'deskripsi_panjang' => 'Program Bimbingan Belajar SMP berfokus pada penguasaan materi esensial di fase transisi akademik. Dengan kurikulum yang terarah, siswa dibimbing membedah konsep abstrak pada Matematika, IPA Terpadu (Fisika & Biologi), dan Bahasa Inggris. Siswa juga dilatih mengasah logika analitis, strategi pemecahan soal berstandar HOTS (Higher Order Thinking Skills), serta persiapan matang untuk seleksi masuk SMA/SMK unggulan.',
            'ikon' => 'book-open',
            'populer' => false,
            'poin' => [
                'Target: Siswa SMP kelas 7-9',
                'Matematika, IPA, dan Bahasa Inggris',
                'Latihan soal bertingkat dan pembahasan tuntas',
                'Persiapan Penilaian Tengah Semester & Ujian Sekolah',
            ],
            'materi' => [
                'Matematika: Aljabar, persamaan & pertidaksamaan linear, geometri, statistika, peluang, dan fungsi kuadrat',
                'IPA Terpadu (Fisika): Besaran & satuan, mekanika gerak, hukum Newton, energi, tekanan, getaran & gelombang',
                'IPA Terpadu (Biologi): Struktur sel, sistem organ tubuh manusia, ekosistem, bioteknologi ramah lingkungan',
                'Bahasa Inggris: Reading comprehension, grammar fundamentals, text genres, and conversational proficiency',
                'Drilling latihan soal HOTS dan trik penyelesaian cepat untuk menghemat waktu ujian',
            ],
            'jadwal' => 'Pilihan hari: Senin, Rabu, Jumat atau Selasa, Kamis, Sabtu • Pukul 16.00 - 17.30 WIB / 18.30 - 20.00 WIB',
            'manfaat' => [
                'Kenaikan nilai rapor sekolah secara signifikan dan konsisten',
                'Kesiapan mental dan akademis prima menghadapi seleksi SMA/SMK favorit',
                'Sesi konsultasi tugas dan PR di luar jam kelas bimbingan reguler',
                'Simulasi tryout berkala dengan analisis diagnostik kelemahan materi tiap siswa',
            ],
            'biaya' => 'Mulai dari Rp450.000 / bulan',
            'rasio' => 'Maksimal 6-8 siswa per kelompok',
        ],

        'sma' => [
            'slug' => 'sma',
            'judul' => 'Bimbingan Belajar SMA',
            'kelas' => 'Kelas 10-12',
            'badge_class' => 'badge-green',
            'deskripsi' => 'Program intensif untuk siswa SMA dengan fokus penguasaan materi dan strategi belajar efektif menghadapi ujian akhir dan seleksi perguruan tinggi.',
            'deskripsi_panjang' => 'Program Bimbingan Belajar SMA di Modern Brilliant berorientasi pada pencapaian prestasi akademik tertinggi di sekolah dan pemantapan portofolio rapor untuk jalur SNBP/SNBT Perguruan Tinggi Negeri. Siswa dibimbing oleh tutor spesialis pada bidang studi Saintek maupun Soshum, dilengkapi modul konsep ringkas, bank soal prediktif, serta layanan konsultasi pemilihan program studi dan kampus impian.',
            'ikon' => 'graduation-cap',
            'populer' => false,
            'poin' => [
                'Target: Siswa SMA kelas 10-12',
                'Materi IPA, IPS, dan peminatan Kurikulum Merdeka',
                'Bimbingan portofolio & strategi pemilihan jurusan PTN',
                'Pendalaman konsep advanced dan bedah rumus cepat',
            ],
            'materi' => [
                'Kelompok Saintek: Matematika Tingkat Lanjut, Fisika Modern, Kimia Organik & Anorganik, Biologi Sel & Genetika',
                'Kelompok Soshum: Ekonomi & Akuntansi, Sosiologi, Geografi Terapan, dan Sejarah Tingkat Lanjut',
                'Mata Pelajaran Wajib: Matematika Dasar, Bahasa Indonesia Kritis, dan Bahasa Inggris Akademik',
                'Strategi penyelesaian soal bertaraf olimpiade dan asesmen sumatif sekolah',
                'Bimbingan personal pemetaan jurusan kuliah berdasarkan minat, bakat, dan analisis nilai rapor',
            ],
            'jadwal' => 'Pilihan sesi: Senin s/d Jumat (16.30 - 18.00 WIB atau 18.30 - 20.15 WIB) • Sabtu: Klinik Konsultasi Tugas',
            'manfaat' => [
                'Meningkatkan rata-rata nilai rapor guna memperbesar peluang lolos SNBP (jalur prestasi)',
                'Pemahaman konsep mendalam yang mempermudah adaptasi materi kuliah kelak',
                'Konseling akademik berkala bersama mentor alumni PTN ternama',
                'Akses eksklusif ke platform latihan soal digital dan modul Modern Brilliant',
            ],
            'biaya' => 'Mulai dari Rp550.000 / bulan',
            'rasio' => 'Maksimal 8-10 siswa per kelompok',
        ],

        'persiapan-ujian' => [
            'slug' => 'persiapan-ujian',
            'aliases' => ['ujian'],
            'judul' => 'Persiapan Ujian',
            'kelas' => 'Kelas 9, 12 & Alumni',
            'badge_class' => 'badge-green',
            'deskripsi' => 'Program khusus persiapan ujian seperti UN, SNBT, dan ujian masuk sekolah favorit. Latihan soal intensif dan pembahasan mendalam.',
            'deskripsi_panjang' => 'Program Persiapan Ujian merupakan program unggulan terakselerasi dengan sistem drilling intensif. Didesain bagi siswa yang menargetkan kelulusan pada SNBT UTBK PTN, Ujian Mandiri (Simak UI, UGM UTUL, dsb.), maupun seleksi masuk SMA Taruna / Unggulan. Dilengkapi sistem Tryout CBT dengan penilaian IRT (Item Response Theory) yang presisi, strategi manajemen waktu, serta bedah soal pola terbaru.',
            'ikon' => 'check-circle',
            'populer' => true,
            'poin' => [
                'Target: UN, SNBT UTBK, Ujian Mandiri, Ujian Masuk Sekolah Favorit',
                'Simulasi & tryout berkala berbasis CBT sistem IRT',
                'Drilling 1.000+ soal prediksi akurat tahun berjalan',
                'Workshop taktik pengerjaan kilat & manajemen waktu ujian',
            ],
            'materi' => [
                'Tes Potensi Skolastik (TPS): Penalaran Umum, Pengetahuan Kuantitatif, Pemahaman Bacaan & Menulis (PBM)',
                'Literasi Bahasa: Literasi dalam Bahasa Indonesia dan Literasi dalam Bahasa Inggris',
                'Penalaran Matematika: Logika analitis kuantitatif dan pemecahan masalah statistika sehari-hari',
                'Bedah tuntas pola soal ujian 5 tahun terakhir dan prediksi materi tren terbaru',
                'Analisis scoring tryout per subtes dengan evaluasi personal untuk memilih prodi realistis',
            ],
            'jadwal' => 'Intensif 4-5 kali seminggu • Pilihan Sesi Pagi (09.00 - 12.00 WIB) / Sesi Sore (16.00 - 19.00 WIB) + Tryout CBT tiap Sabtu',
            'manfaat' => [
                'Peluang lolos PTN & sekolah impian terbukti meningkat drastis dengan metode drilling teruji',
                'Kemampuan mengenali pola soal dan menghemat hingga 40% waktu pengerjaan',
                'Laporan skor tryout real-time dengan grafik evaluasi daya saing nasional',
                'Sesi motivasi, konseling mental, dan manajemen stres menghadapi ujian penentu masa depan',
            ],
            'biaya' => 'Mulai dari Rp650.000 / bulan',
            'rasio' => 'Kelas intensif fokus 8-12 siswa',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Template Jadwal Pembelajaran Mingguan
    |--------------------------------------------------------------------------
    |
    | Data jadwal mingguan bimbel. Hari ke: 1 = Senin s/d 6 = Sabtu.
    |
    */
    'jadwal' => [
        // SENIN (Hari ke-1)
        [
            'hari_ke' => 1,
            'jam_mulai' => '15.30',
            'jam_selesai' => '17.00',
            'mapel' => 'Matematika SD',
            'kelas' => 'Kelas 4-6',
            'tipe' => 'Reguler',
            'jenjang' => 'sd',
            'pengajar' => 'Bu Sari',
            'warna_ikon' => 'blue',
            'program_slug' => 'sd',
        ],
        [
            'hari_ke' => 1,
            'jam_mulai' => '16.00',
            'jam_selesai' => '17.30',
            'mapel' => 'IPA Terpadu SMP',
            'kelas' => 'Kelas 8-9',
            'tipe' => 'Reguler',
            'jenjang' => 'smp',
            'pengajar' => 'Pak Budi',
            'warna_ikon' => 'green',
            'program_slug' => 'smp',
        ],
        [
            'hari_ke' => 1,
            'jam_mulai' => '16.30',
            'jam_selesai' => '18.00',
            'mapel' => 'Matematika SMA',
            'kelas' => 'Kelas 12',
            'tipe' => 'Reguler',
            'jenjang' => 'sma',
            'pengajar' => 'Pak Hendra',
            'warna_ikon' => 'blue',
            'program_slug' => 'sma',
        ],
        [
            'hari_ke' => 1,
            'jam_mulai' => '18.30',
            'jam_selesai' => '20.00',
            'mapel' => 'Penalaran Kuantitatif UTBK',
            'kelas' => 'Kelas 12 & Alumni',
            'tipe' => 'Intensif UTBK',
            'jenjang' => 'utbk',
            'pengajar' => 'Kak Dimas',
            'warna_ikon' => 'purple',
            'program_slug' => 'persiapan-ujian',
        ],

        // SELASA (Hari ke-2)
        [
            'hari_ke' => 2,
            'jam_mulai' => '15.30',
            'jam_selesai' => '17.00',
            'mapel' => 'Bahasa Indonesia SD',
            'kelas' => 'Kelas 1-3',
            'tipe' => 'Calistung & Literasi',
            'jenjang' => 'sd',
            'pengajar' => 'Bu Ratna',
            'warna_ikon' => 'orange',
            'program_slug' => 'sd',
        ],
        [
            'hari_ke' => 2,
            'jam_mulai' => '16.00',
            'jam_selesai' => '17.30',
            'mapel' => 'Bahasa Inggris SMP',
            'kelas' => 'Kelas 7-9',
            'tipe' => 'Reguler',
            'jenjang' => 'smp',
            'pengajar' => 'Miss Nadia',
            'warna_ikon' => 'purple',
            'program_slug' => 'smp',
        ],
        [
            'hari_ke' => 2,
            'jam_mulai' => '16.30',
            'jam_selesai' => '18.00',
            'mapel' => 'Fisika SMA',
            'kelas' => 'Kelas 11-12',
            'tipe' => 'Intensif',
            'jenjang' => 'sma',
            'pengajar' => 'Pak Budi',
            'warna_ikon' => 'red',
            'program_slug' => 'sma',
        ],
        [
            'hari_ke' => 2,
            'jam_mulai' => '18.30',
            'jam_selesai' => '20.00',
            'mapel' => 'Literasi Bahasa Indonesia & Inggris',
            'kelas' => 'Kelas 12 & Alumni',
            'tipe' => 'Intensif UTBK',
            'jenjang' => 'utbk',
            'pengajar' => 'Kak Dimas',
            'warna_ikon' => 'orange',
            'program_slug' => 'persiapan-ujian',
        ],

        // RABU (Hari ke-3)
        [
            'hari_ke' => 3,
            'jam_mulai' => '15.30',
            'jam_selesai' => '17.00',
            'mapel' => 'Sains & Eksperimen Seru SD',
            'kelas' => 'Kelas 4-6',
            'tipe' => 'Fun Learning',
            'jenjang' => 'sd',
            'pengajar' => 'Bu Sari',
            'warna_ikon' => 'green',
            'program_slug' => 'sd',
        ],
        [
            'hari_ke' => 3,
            'jam_mulai' => '16.00',
            'jam_selesai' => '17.30',
            'mapel' => 'Matematika SMP',
            'kelas' => 'Kelas 7-8',
            'tipe' => 'Aljabar & Geometri',
            'jenjang' => 'smp',
            'pengajar' => 'Pak Hendra',
            'warna_ikon' => 'blue',
            'program_slug' => 'smp',
        ],
        [
            'hari_ke' => 3,
            'jam_mulai' => '16.30',
            'jam_selesai' => '18.00',
            'mapel' => 'Kimia SMA',
            'kelas' => 'Kelas 11-12',
            'tipe' => 'Reguler',
            'jenjang' => 'sma',
            'pengajar' => 'Bu Ratna',
            'warna_ikon' => 'orange',
            'program_slug' => 'sma',
        ],
        [
            'hari_ke' => 3,
            'jam_mulai' => '18.30',
            'jam_selesai' => '20.00',
            'mapel' => 'Penalaran Umum & TPS UTBK',
            'kelas' => 'Kelas 12 & Alumni',
            'tipe' => 'Intensif UTBK',
            'jenjang' => 'utbk',
            'pengajar' => 'Kak Dimas',
            'warna_ikon' => 'purple',
            'program_slug' => 'persiapan-ujian',
        ],

        // KAMIS (Hari ke-4)
        [
            'hari_ke' => 4,
            'jam_mulai' => '15.30',
            'jam_selesai' => '17.00',
            'mapel' => 'Matematika Dasar SD',
            'kelas' => 'Kelas 1-3',
            'tipe' => 'Dasar Berhitung',
            'jenjang' => 'sd',
            'pengajar' => 'Bu Sari',
            'warna_ikon' => 'blue',
            'program_slug' => 'sd',
        ],
        [
            'hari_ke' => 4,
            'jam_mulai' => '16.00',
            'jam_selesai' => '17.30',
            'mapel' => 'Biologi SMP',
            'kelas' => 'Kelas 8-9',
            'tipe' => 'Pendalaman Materi',
            'jenjang' => 'smp',
            'pengajar' => 'Bu Ratna',
            'warna_ikon' => 'green',
            'program_slug' => 'smp',
        ],
        [
            'hari_ke' => 4,
            'jam_mulai' => '16.30',
            'jam_selesai' => '18.00',
            'mapel' => 'Biologi SMA',
            'kelas' => 'Kelas 11-12',
            'tipe' => 'Saintek',
            'jenjang' => 'sma',
            'pengajar' => 'Bu Ratna',
            'warna_ikon' => 'green',
            'program_slug' => 'sma',
        ],
        [
            'hari_ke' => 4,
            'jam_mulai' => '18.30',
            'jam_selesai' => '20.00',
            'mapel' => 'Drilling Soal HOTS UTBK',
            'kelas' => 'Kelas 12 & Alumni',
            'tipe' => 'Intensif UTBK',
            'jenjang' => 'utbk',
            'pengajar' => 'Pak Hendra',
            'warna_ikon' => 'red',
            'program_slug' => 'persiapan-ujian',
        ],

        // JUMAT (Hari ke-5)
        [
            'hari_ke' => 5,
            'jam_mulai' => '15.30',
            'jam_selesai' => '17.00',
            'mapel' => 'Pembahasan PR & Tugas SD',
            'kelas' => 'Kelas 1-6',
            'tipe' => 'Klinik PR',
            'jenjang' => 'sd',
            'pengajar' => 'Bu Sari',
            'warna_ikon' => 'orange',
            'program_slug' => 'sd',
        ],
        [
            'hari_ke' => 5,
            'jam_mulai' => '16.00',
            'jam_selesai' => '17.30',
            'mapel' => 'Persiapan Ujian Sekolah SMP',
            'kelas' => 'Kelas 9',
            'tipe' => 'Asesmen Sekolah',
            'jenjang' => 'smp',
            'pengajar' => 'Pak Budi',
            'warna_ikon' => 'red',
            'program_slug' => 'smp',
        ],
        [
            'hari_ke' => 5,
            'jam_mulai' => '16.30',
            'jam_selesai' => '18.00',
            'mapel' => 'Ekonomi & Soshum SMA',
            'kelas' => 'Kelas 11-12',
            'tipe' => 'Peminatan',
            'jenjang' => 'sma',
            'pengajar' => 'Pak Hendra',
            'warna_ikon' => 'purple',
            'program_slug' => 'sma',
        ],
        [
            'hari_ke' => 5,
            'jam_mulai' => '18.30',
            'jam_selesai' => '20.00',
            'mapel' => 'Bedah Pola Soal SNBT',
            'kelas' => 'Kelas 12 & Alumni',
            'tipe' => 'Intensif UTBK',
            'jenjang' => 'utbk',
            'pengajar' => 'Kak Dimas',
            'warna_ikon' => 'blue',
            'program_slug' => 'persiapan-ujian',
        ],

        // SABTU (Hari ke-6)
        [
            'hari_ke' => 6,
            'jam_mulai' => '09.00',
            'jam_selesai' => '10.30',
            'mapel' => 'Klinik Matematika & Sains SD',
            'kelas' => 'Kelas 4-6',
            'tipe' => 'Konsultasi Terbuka',
            'jenjang' => 'sd',
            'pengajar' => 'Bu Sari',
            'warna_ikon' => 'blue',
            'program_slug' => 'sd',
        ],
        [
            'hari_ke' => 6,
            'jam_mulai' => '09.00',
            'jam_selesai' => '11.00',
            'mapel' => 'Tryout Berkala SMP',
            'kelas' => 'Kelas 7-9',
            'tipe' => 'Simulasi Ujian',
            'jenjang' => 'smp',
            'pengajar' => 'Tim Tutor SMP',
            'warna_ikon' => 'red',
            'program_slug' => 'smp',
        ],
        [
            'hari_ke' => 6,
            'jam_mulai' => '10.30',
            'jam_selesai' => '12.00',
            'mapel' => 'Klinik Fisika & Kimia SMA',
            'kelas' => 'Kelas 10-12',
            'tipe' => 'Konsultasi Privat',
            'jenjang' => 'sma',
            'pengajar' => 'Pak Budi',
            'warna_ikon' => 'green',
            'program_slug' => 'sma',
        ],
        [
            'hari_ke' => 6,
            'jam_mulai' => '13.00',
            'jam_selesai' => '16.00',
            'mapel' => 'Tryout Akbar CBT IRT Nasional',
            'kelas' => 'Kelas 12 & Alumni',
            'tipe' => 'Tryout CBT UTBK',
            'jenjang' => 'utbk',
            'pengajar' => 'Tim Master Tutor',
            'warna_ikon' => 'purple',
            'program_slug' => 'persiapan-ujian',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Informasi "Tentang Kami"
    |--------------------------------------------------------------------------
    |
    | Profil, Visi & Misi, Nilai Utama, Tim Pengajar, dan Fasilitas.
    |
    */
    'tentang_kami' => [
        'profil' => [
            'tagline' => 'Tentang Bimbel Modern Brilliant',
            'judul' => 'Membimbing Generasi Cerdas dan Berkarakter Sejak 2018',
            'ringkasan' => 'Bimbel Modern Brilliant didirikan dengan tekad kuat untuk menghadirkan bimbingan belajar berkualitas tinggi yang tidak hanya berfokus pada hafalan rumus kilat, melainkan membangun fondasi pemahaman konsep mendalam, daya kritis, dan karakter percaya diri pada setiap siswa.',
            'cerita' => 'Bermula dari kelompok bimbingan belajar kecil yang digagas oleh para alumni dan akademisi perguruan tinggi terkemuka di Indonesia, kini Modern Brilliant telah berkembang menjadi mitra belajar terpercaya bagi ribuan siswa dan orang tua. Kami meyakini bahwa setiap siswa adalah pribadi berharga yang unik dengan gaya belajar serta kecepatan berpikir berbeda. Oleh karena itu, pendekatan kami selalu bersifat personal, adaptif, sabar, dan interaktif.',
        ],

        'statistik' => [
            [
                'angka' => '1.500+',
                'label' => 'Siswa Terbimbing',
                'sub' => 'Dari jenjang SD, SMP, hingga SMA',
            ],
            [
                'angka' => '94.8%',
                'label' => 'Lolos Kampus Impian',
                'sub' => 'Melalui jalur SNBP, SNBT, & Ujian Mandiri',
            ],
            [
                'angka' => '45+',
                'label' => 'Tutor Berpengalaman',
                'sub' => 'Lulusan PTN ternama & bersertifikasi',
            ],
            [
                'angka' => '8+ Tahun',
                'label' => 'Dedikasi Pengabdian',
                'sub' => 'Membangun ekosistem belajar suportif',
            ],
        ],

        'visi' => 'Menjadi lembaga bimbingan belajar modern nomor satu di Indonesia yang melahirkan generasi muda berdaya saing global, berpikir kritis, mandiri, dan berkarakter mulia melalui pembelajaran adaptif yang memanusiakan siswa.',

        'misi' => [
            'Menyelenggarakan sistem pendampingan belajar yang personal, terstruktur, dan berbasis pemahaman konsep fundamental.',
            'Menghadirkan tenaga pengajar profesional yang kompeten, berempati, inspiratif, dan selalu siap membimbing dengan sabar.',
            'Mengembangkan materi ajar mutakhir, bank soal prediktif, serta teknologi asesmen adaptif (Computer Based Test) berbasis IRT.',
            'Membangun komunikasi dan kemitraan harmonis yang transparan bersama orang tua guna memantau pertumbuhan akademik siswa secara berkala.',
        ],

        'nilai_utama' => [
            [
                'judul' => 'Pendekatan Personal',
                'deskripsi' => 'Rasio kelas kecil memungkinkan tutor mengenali keunikan, kesulitan, dan potensi individual setiap murid secara optimal.',
                'ikon' => 'users',
                'warna' => 'green',
            ],
            [
                'judul' => 'Konseptual & Logis',
                'deskripsi' => 'Kami menolak hafalan rumus buta. Siswa diajak memahami alasan di balik setiap rumus agar materi melekat dalam ingatan jangka panjang.',
                'ikon' => 'book-open',
                'warna' => 'blue',
            ],
            [
                'judul' => 'Lingkungan Suportif',
                'deskripsi' => 'Suasana kelas yang hangat dan apresiatif membuat murid tidak takut salah, percaya diri bertanya, serta nyaman berdiskusi aktif.',
                'ikon' => 'smile',
                'warna' => 'orange',
            ],
            [
                'judul' => 'Transparansi Evaluasi',
                'deskripsi' => 'Orang tua mendapatkan laporan berkala setiap bulan yang mencakup grafik skor tryout, kedisiplinan, dan catatan tutor.',
                'ikon' => 'chart',
                'warna' => 'purple',
            ],
        ],

        'tutor' => [
            [
                'nama' => 'Sari Wahyuni, M.Pd.',
                'peran' => 'Head of Academic & Tutor Matematika SD-SMP',
                'lulusan' => 'Magister Pendidikan Matematika UNJ',
                'pengalaman' => '8 Tahun Pengalaman Mengajar',
                'inisial' => 'SW',
                'warna' => 'blue',
            ],
            [
                'nama' => 'Budi Santoso, S.Si.',
                'peran' => 'Master Tutor Fisika & IPA Terpadu',
                'lulusan' => 'Fisika MIPA Universitas Indonesia',
                'pengalaman' => '7 Tahun Pengalaman Mengajar',
                'inisial' => 'BS',
                'warna' => 'green',
            ],
            [
                'nama' => 'Dimas Prasetyo, S.T.',
                'peran' => 'Spesialis Penalaran Kuantitatif & TPS UTBK',
                'lulusan' => 'Teknik Industri Institut Teknologi Bandung',
                'pengalaman' => '6 Tahun Pengalaman Mengajar',
                'inisial' => 'DP',
                'warna' => 'purple',
            ],
            [
                'nama' => 'Ratna Kusuma, S.Pd.',
                'peran' => 'Spesialis Kimia & Biologi SMA',
                'lulusan' => 'Pendidikan Kimia Universitas Pendidikan Indonesia',
                'pengalaman' => '9 Tahun Pengalaman Mengajar',
                'inisial' => 'RK',
                'warna' => 'orange',
            ],
            [
                'nama' => 'Nadia Amanda, M.Hum.',
                'peran' => 'Tutor Bahasa Inggris & Literasi Kritis',
                'lulusan' => 'Sastra Inggris Universitas Gadjah Mada',
                'pengalaman' => '5 Tahun Pengalaman Mengajar',
                'inisial' => 'NA',
                'warna' => 'red',
            ],
            [
                'nama' => 'Hendra Pratama, S.E.',
                'peran' => 'Spesialis Ekonomi, Akuntansi & Soshum',
                'lulusan' => 'Ilmu Ekonomi Universitas Airlangga',
                'pengalaman' => '6 Tahun Pengalaman Mengajar',
                'inisial' => 'HP',
                'warna' => 'blue',
            ],
        ],

        'fasilitas' => [
            [
                'nama' => 'Ruang Belajar Ergonomis & Ber-AC',
                'deskripsi' => 'Didesain hening dengan pencahayaan optimal dan pendingin ruangan untuk menjaga konsentrasi belajar maksimal.',
                'ikon' => 'building',
            ],
            [
                'nama' => 'Modul Belajar Eksklusif & Bank Soal',
                'deskripsi' => 'Disusun langsung oleh tim riset kurikulum Modern Brilliant dengan pembahasan bertingkat dari dasar hingga level HOTS.',
                'ikon' => 'book',
            ],
            [
                'nama' => 'Sistem CBT Adaptif IRT Nasional',
                'deskripsi' => 'Platform simulasi ujian digital dengan sistem penilaian Item Response Theory (IRT) yang identik dengan tes SNBT resmi.',
                'ikon' => 'laptop',
            ],
            [
                'nama' => 'Klinik Konsultasi & Tanya PR Gratis',
                'deskripsi' => 'Fasilitas bimbingan ekstra di luar jam kelas bimbingan reguler bagi siswa yang ingin menuntaskan tugas sekolahnya.',
                'ikon' => 'chat',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Informasi Kontak & Layanan
    |--------------------------------------------------------------------------
    |
    | Alamat, Call Center, WhatsApp, Email, Jam Kerja, dan FAQ.
    |
    */
    'kontak' => [
        'alamat' => 'Jl. Pendidikan Utama No. 88, Kebayoran Baru, Jakarta Selatan — 12340',
        'telepon' => '(021) 7890-1234',
        'whatsapp' => '0812-3456-7890',
        'whatsapp_link' => 'https://wa.me/6281234567890',
        'email' => 'info@modernbrilliant.sch.id',
        'jam_operasional' => 'Senin – Sabtu, 08.00 – 20.00 WIB',
        'maps_embed' => 'https://maps.google.com/maps?q=Jakarta+Selatan&t=&z=13&ie=UTF8&iwloc=&output=embed',
        'faq' => [
            [
                'tanya' => 'Bagaimana cara mendaftar program di Bimbel Modern Brilliant?',
                'jawab' => 'Pendaftaran dapat dilakukan secara online melalui formulir di halaman ini, menghubungi admin WhatsApp di 0812-3456-7890, atau datang langsung ke kantor cabang kami.',
            ],
            [
                'tanya' => 'Apakah ada tes diagnostik atau pemetaan kemampuan awal?',
                'jawab' => 'Ya, setiap siswa baru akan mengikuti tes diagnostik awal tanpa dipungut biaya guna memetakan kekuatan dan kelemahan materi agar tutor dapat menyusun strategi bimbingan yang tepat.',
            ],
            [
                'tanya' => 'Apakah tersedia sesi uji coba (Free Trial Class)?',
                'jawab' => 'Kami menyediakan 1x sesi coba kelas gratis agar siswa dapat merasakan langsung metode pembelajaran dan kecocokan interaksi dengan tutor sebelum melakukan pendaftaran resmi.',
            ],
            [
                'tanya' => 'Bagaimana jika siswa berhalangan hadir pada jadwal sesi tertentu?',
                'jawab' => 'Siswa dapat mengajukan izin minimal 6 jam sebelum sesi dimulai dan berhak mendapatkan sesi pengganti (makeup class) atau konsultasi materi di klinik PR.',
            ],
        ],
    ],
];



