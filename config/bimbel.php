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
];
