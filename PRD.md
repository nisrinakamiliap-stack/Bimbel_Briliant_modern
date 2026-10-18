# PRODUCT REQUIREMENTS DOCUMENT (PRD)
# BIMBEL Sentra Edukasi
## Sistem Informasi Bimbingan Belajar Berbasis Web

**Versi:** 1.0  
**Jenis aplikasi:** Website publik dan sistem manajemen pembelajaran  
**Nama produk:** Bimbel Sentra Edukasi  
**Target implementasi:** Laravel  
**Dokumen acuan:** Use Case dan ERD Bimbel Sentra Edukasi

---

# 1. RINGKASAN PRODUK

## 1.1 Latar Belakang

Bimbel Sentra Edukasi merupakan sistem informasi bimbingan belajar berbasis web yang dirancang untuk membantu pengelolaan program bimbel, pendaftaran siswa, pembayaran, kelas, jadwal, materi pembelajaran, tugas, ujian, penilaian, dan absensi dalam satu platform terintegrasi.

Sistem ini menyediakan tiga jenis pengguna utama, yaitu Admin, Guru/Tentor, dan Murid/Siswa. Setiap pengguna memiliki dashboard, fitur, dan hak akses sesuai perannya.

Website juga menyediakan halaman publik agar calon siswa dapat mengenal lembaga, melihat program bimbel, memeriksa jadwal, dan mendaftar secara online.

## 1.2 Tujuan Produk

1. Menyediakan informasi program bimbel secara online.
2. Mempermudah calon siswa melakukan pendaftaran.
3. Mengelola pembayaran dan verifikasi oleh admin.
4. Mempermudah guru mengelola kegiatan pembelajaran.
5. Memungkinkan siswa mengakses materi, tugas, ujian, nilai, dan absensi.
6. Menyediakan data terpusat untuk pengelolaan operasional bimbel.
7. Mengurangi proses administrasi manual.
8. Menyediakan laporan untuk membantu pengambilan keputusan.

## 1.3 Target Pengguna

### Admin
Pengelola operasional yang bertanggung jawab terhadap pengguna, program, kelas, jadwal, pendaftaran, pembayaran, dan laporan.

### Guru/Tentor
Tenaga pengajar yang bertanggung jawab terhadap kegiatan pembelajaran, materi, tugas, ujian, penilaian, absensi, dan komunikasi.

### Murid/Siswa
Peserta bimbel yang menggunakan sistem untuk mendaftar program, mengikuti kelas, mengakses materi, mengerjakan tugas dan ujian, melihat nilai, serta memantau absensi.

### Pengunjung
Pengguna yang belum login dan ingin mengetahui informasi lembaga, program, jadwal, serta cara pendaftaran.

---

# 2. TEKNOLOGI DAN ARSITEKTUR

Gunakan teknologi berikut:

- Backend: Laravel sesuai versi yang sudah terpasang.
- Frontend: Blade, HTML, CSS, dan JavaScript.
- Build tools: Vite.
- Database: gunakan database yang sudah dikonfigurasi pada project.
- ORM: Eloquent.
- Authentication: gunakan mekanisme autentikasi Laravel yang sesuai dengan kondisi project.
- Styling: CSS terstruktur atau framework yang sudah digunakan project.
- Version control: pertahankan kompatibilitas dengan Git jika project sudah menggunakannya.

## Ketentuan arsitektur

1. Gunakan pola MVC Laravel.
2. Pisahkan model, controller, request validation, middleware, policy, dan view sesuai tanggung jawabnya.
3. Gunakan reusable Blade layouts dan components.
4. Pisahkan route publik dan route yang memerlukan autentikasi.
5. Terapkan role-based access control pada backend.
6. Gunakan Eloquent untuk akses database.
7. Gunakan pagination untuk daftar data yang panjang.
8. Jangan menaruh seluruh logika bisnis dalam satu controller.
9. Jangan mengganti teknologi atau arsitektur project yang sudah berjalan tanpa alasan kuat.
10. Jangan membuat API terpisah jika tidak diperlukan oleh arsitektur yang sudah ada.

---

# 3. DESAIN UI/UX

## 3.1 Konsep Desain

Buat website edukasi yang modern, profesional, ramah, dan mudah digunakan oleh siswa maupun orang tua.

Arah visual:
- Background dominan putih.
- Warna utama hijau untuk identitas pendidikan dan aksi utama.
- Warna navy untuk teks dan navigasi.
- Aksen biru untuk elemen pendukung.
- Card dengan sudut membulat.
- Tombol dengan ukuran nyaman dan hierarki jelas.
- Whitespace yang cukup agar halaman tidak sesak.
- Ikon yang konsisten.
- Tipografi mudah dibaca.
- Animasi ringan hanya jika meningkatkan pengalaman pengguna.

Gunakan website edukasi seperti Duolingo sebagai inspirasi umum untuk keramahan visual dan penggunaan card. Jangan menyalin logo, aset, atau identitas mereknya.

## 3.2 Responsivitas

Website wajib mendukung:
- Desktop.
- Laptop.
- Tablet.
- Smartphone.

Pada perangkat mobile, navigasi harus mudah digunakan, tabel dapat disesuaikan atau diganti menjadi card, dan formulir tidak boleh terpotong.

## 3.3 Konsistensi

Semua halaman harus menggunakan sistem desain yang konsisten, meliputi warna, ukuran tombol, input, card, tabel, modal, notifikasi, dan navigasi.

Jangan membuat setiap halaman dengan desain yang berbeda-beda.

---

# 4. STRUKTUR HALAMAN PUBLIK

## 4.1 Home

Tujuan: memperkenalkan Bimbel Sentra Edukasi dan mengarahkan pengunjung menuju program atau pendaftaran.

Komponen:
- Navbar dengan logo, Home, Program, Jadwal, Tentang Kami, Kontak, Login, dan Daftar.
- Hero section dengan judul utama, deskripsi singkat, dan CTA.
- Ringkasan keunggulan bimbel.
- Daftar program unggulan.
- Alur pendaftaran.
- Keunggulan metode pembelajaran.
- Statistik lembaga jika data yang valid tersedia.
- Testimoni hanya jika data asli tersedia.
- CTA untuk melihat program atau mendaftar.
- Footer berisi informasi lembaga dan tautan penting.

Jangan menampilkan angka, testimoni, atau prestasi palsu sebagai fakta.

## 4.2 Program Bimbel

Fitur:
- Menampilkan daftar program yang aktif.
- Card program berisi nama, kategori, jenjang, deskripsi singkat, harga, dan durasi.
- Filter berdasarkan kategori atau jenjang.
- Pencarian program.
- Pagination jika data banyak.
- Tombol Lihat Detail.

Data program harus berasal dari database, bukan hardcode setelah backend diimplementasikan.

## 4.3 Detail Program

Menampilkan:
- Nama program.
- Deskripsi lengkap.
- Kategori dan jenjang.
- Harga dan durasi.
- Informasi kelas yang tersedia.
- Jadwal yang relevan jika sudah tersedia.
- Kapasitas atau ketersediaan kelas jika relevan.
- Tombol Daftar Program.

Pengunjung yang belum login diarahkan ke login atau registrasi dengan alur kembali ke program yang dipilih.

## 4.4 Jadwal

Menampilkan jadwal program atau kelas yang tersedia untuk publik.

Fitur:
- Filter program.
- Filter hari atau tanggal.
- Informasi waktu, kelas, mode pembelajaran, dan status ketersediaan.
- Detail jadwal.

Jangan menampilkan data pribadi siswa atau informasi internal guru kepada pengunjung.

## 4.5 Tentang Kami

Menampilkan:
- Profil Bimbel Sentra Edukasi.
- Visi dan misi jika sudah ditentukan.
- Metode pembelajaran.
- Keunggulan lembaga.
- Informasi pendukung yang valid.

Konten harus dapat disesuaikan tanpa mengubah logika utama aplikasi.

## 4.6 Kontak

Menampilkan:
- Informasi kontak lembaga.
- Nomor telepon atau WhatsApp jika sudah ditentukan.
- Email jika tersedia.
- Alamat jika tersedia.
- Formulir pesan.

Validasi input dan tampilkan konfirmasi pengiriman. Jangan menampilkan kontak fiktif sebagai informasi resmi.

## 4.7 Login

Fitur:
- Email.
- Password.
- Toggle tampil/sembunyikan password.
- Validasi input.
- Pesan error yang jelas.
- Link ke registrasi.
- Link lupa password jika fitur tersebut sudah dikonfigurasi.

Setelah login berhasil, arahkan pengguna ke dashboard berdasarkan role.

## 4.8 Registrasi

Registrasi publik ditujukan untuk calon siswa.

Field minimal:
- Nama lengkap.
- Email.
- Nomor telepon.
- Password.
- Konfirmasi password.

Validasi:
- Email harus unik.
- Password harus memenuhi kebijakan keamanan.
- Konfirmasi password harus sesuai.
- Password disimpan dalam bentuk hash.
- Role tidak boleh ditentukan sendiri oleh pengguna melalui request.

Akun publik baru harus memiliki role murid. Akun admin dan guru dibuat atau ditetapkan melalui mekanisme berwenang.

---

# 5. ROLE DAN HAK AKSES

## 5.1 Admin

Admin memiliki akses untuk:
- Dashboard admin.
- Manajemen murid.
- Manajemen guru.
- Manajemen program.
- Manajemen kelas.
- Manajemen jadwal.
- Manajemen pendaftaran.
- Verifikasi pembayaran.
- Pengelolaan laporan.
- Pengaturan sistem yang tersedia.
- Pengelolaan komunikasi jika diperlukan.

## 5.2 Guru/Tentor

Guru memiliki akses untuk:
- Dashboard guru.
- Melihat kelas yang ditugaskan.
- Melihat jadwal mengajar.
- Mengelola materi pada kelas yang diampu.
- Membuat dan mengelola tugas.
- Memeriksa pengumpulan tugas.
- Memberikan nilai dan feedback.
- Membuat ujian dan soal.
- Memeriksa hasil ujian.
- Mencatat absensi.
- Mengirim pengumuman.
- Berkomunikasi dengan siswa yang relevan.

Guru tidak boleh mengakses atau mengubah data kelas yang bukan kewenangannya.

## 5.3 Murid/Siswa

Murid memiliki akses untuk:
- Dashboard murid.
- Melihat dan memilih program.
- Melakukan pendaftaran.
- Melihat status pendaftaran.
- Melihat pembayaran.
- Mengakses kelas yang menjadi haknya.
- Melihat jadwal.
- Mengakses materi.
- Mengumpulkan tugas.
- Mengikuti ujian.
- Melihat nilai.
- Melihat absensi.
- Membaca notifikasi.
- Menggunakan chat atau konsultasi yang tersedia.

Murid tidak boleh mengakses data pribadi, pembayaran, pengumpulan tugas, atau hasil ujian milik murid lain.

## 5.4 Pengunjung

Pengunjung hanya dapat mengakses halaman publik, program yang dipublikasikan, jadwal publik, informasi lembaga, kontak, login, dan registrasi.

---

# 6. DASHBOARD ADMIN

## 6.1 Ringkasan Dashboard

Tampilkan statistik yang dihitung dari database:
- Total murid.
- Total guru.
- Total program aktif.
- Total kelas aktif.
- Pendaftaran yang menunggu verifikasi.
- Pembayaran yang menunggu verifikasi.
- Ringkasan transaksi.
- Aktivitas terbaru yang relevan.

Statistik tidak boleh menggunakan angka hardcode setelah database terhubung.

## 6.2 Manajemen Murid

Admin dapat:
- Melihat daftar murid.
- Mencari murid berdasarkan nama atau email.
- Melihat detail profil.
- Menambahkan atau memperbarui data sesuai aturan.
- Mengaktifkan atau menonaktifkan akun.
- Melihat pendaftaran dan riwayat pembelajaran yang relevan.

Penghapusan akun harus mempertimbangkan riwayat transaksi dan pembelajaran.

## 6.3 Manajemen Guru

Admin dapat:
- Melihat daftar guru.
- Membuat akun guru.
- Mengedit profil dan keahlian.
- Mengaktifkan atau menonaktifkan akun.
- Menetapkan guru ke kelas.
- Melihat kelas dan jadwal yang diampu.

## 6.4 Manajemen Program

Admin dapat:
- Membuat program.
- Mengedit program.
- Melihat detail program.
- Mengatur kategori, jenjang, harga, durasi, dan deskripsi.
- Mengaktifkan atau menonaktifkan program.
- Mengatur visibilitas program publik.

Program yang telah memiliki transaksi atau pendaftaran historis tidak boleh dihapus sembarangan.

## 6.5 Manajemen Kelas

Admin dapat:
- Membuat kelas.
- Menghubungkan kelas dengan program.
- Menentukan guru pengajar.
- Mengatur kapasitas.
- Menentukan mode pembelajaran online atau offline.
- Mengatur status kelas.
- Melihat daftar murid yang terdaftar.

Sistem harus memvalidasi kapasitas dan menghindari pendaftaran yang melebihi kuota.

## 6.6 Manajemen Jadwal

Admin dapat:
- Membuat jadwal kelas.
- Mengubah jadwal.
- Melihat jadwal per kelas atau guru.
- Mendeteksi bentrok jadwal yang relevan.
- Membatalkan atau memperbarui jadwal sesuai aturan.

## 6.7 Manajemen Pembayaran

Admin dapat:
- Melihat pembayaran masuk.
- Melihat bukti pembayaran.
- Memeriksa nominal.
- Menyetujui pembayaran.
- Menolak pembayaran dengan alasan.
- Melihat status dan riwayat verifikasi.

Perubahan status harus tercatat. Sistem tidak boleh menganggap pembayaran valid hanya karena bukti telah diunggah.

## 6.8 Laporan

Sediakan laporan:
- Data murid.
- Data guru.
- Data program dan kelas.
- Pendaftaran.
- Pembayaran.
- Kehadiran.
- Nilai jika diperlukan.

Sediakan filter periode dan status sesuai jenis laporan. Jika ekspor data ditambahkan, pastikan hanya pengguna berwenang yang dapat mengaksesnya.

---

# 7. DASHBOARD GURU/TENTOR

## 7.1 Dashboard

Tampilkan:
- Kelas yang diampu.
- Jadwal mengajar mendatang.
- Tugas yang dibuat.
- Pengumpulan tugas yang perlu dinilai.
- Ujian yang aktif.
- Ringkasan absensi.
- Aktivitas pembelajaran terbaru.

## 7.2 Materi

Guru dapat:
- Membuat materi.
- Mengedit materi.
- Menghapus atau mengarsipkan materi sesuai kebijakan.
- Mengunggah file atau memasukkan tautan.
- Menghubungkan materi ke kelas.
- Menentukan status publikasi.

Hanya murid yang berhak mengikuti kelas yang dapat mengakses materi privat kelas tersebut.

## 7.3 Tugas

Guru dapat:
- Membuat tugas.
- Menentukan judul dan instruksi.
- Mengatur tenggat.
- Mengunggah lampiran.
- Melihat daftar pengumpulan.
- Memberikan nilai.
- Memberikan feedback.
- Memantau keterlambatan pengumpulan.

## 7.4 Ujian

Guru dapat:
- Membuat ujian atau kuis.
- Mengatur jadwal dan durasi.
- Membuat soal.
- Menentukan jenis soal.
- Mengatur pilihan jawaban jika diperlukan.
- Menentukan bobot nilai.
- Melihat hasil pengerjaan siswa.

Kunci jawaban tidak boleh dikirim ke browser murid sebelum diperbolehkan oleh aturan ujian.

## 7.5 Absensi

Guru dapat:
- Melihat jadwal kelas.
- Mencatat kehadiran siswa.
- Menentukan status hadir, izin, sakit, atau alpa.
- Menambahkan keterangan.
- Melihat riwayat absensi.

Cegah duplikasi catatan absensi untuk murid dan jadwal yang sama, kecuali sistem memang menyediakan mekanisme koreksi.

## 7.6 Komunikasi

Guru dapat mengirim pengumuman atau berkomunikasi dengan siswa sesuai hubungan kelas dan hak aksesnya.

---

# 8. DASHBOARD MURID

## 8.1 Dashboard

Tampilkan:
- Kelas yang sedang diikuti.
- Jadwal belajar mendatang.
- Materi terbaru.
- Tugas dengan tenggat terdekat.
- Ujian yang tersedia.
- Ringkasan nilai.
- Ringkasan absensi.
- Status pembayaran.
- Notifikasi terbaru.

Dashboard hanya menampilkan data milik murid yang sedang login dan kelas yang menjadi haknya.

## 8.2 Pendaftaran Program

Alur:
1. Murid memilih program.
2. Murid melihat detail program.
3. Murid memilih kelas yang tersedia.
4. Sistem memvalidasi status kelas dan kapasitas.
5. Sistem menyimpan pendaftaran.
6. Murid melihat instruksi pembayaran.
7. Murid melakukan pembayaran sesuai metode yang disediakan.
8. Murid mengunggah bukti pembayaran jika pembayaran manual digunakan.
9. Admin melakukan verifikasi.
10. Murid menerima perubahan status.
11. Akses kelas diberikan setelah syarat pendaftaran terpenuhi.

Sistem harus mencegah pendaftaran ganda dan menangani perubahan kapasitas dengan aman.

## 8.3 Materi Pembelajaran

Murid dapat:
- Melihat materi kelasnya.
- Membuka detail materi.
- Mengakses file atau tautan yang diizinkan.
- Melihat informasi publikasi materi.

## 8.4 Pengumpulan Tugas

Murid dapat:
- Melihat instruksi tugas.
- Melihat tenggat.
- Mengunggah jawaban atau file.
- Mengirim pengumpulan.
- Melihat status pengumpulan.
- Melihat nilai dan feedback setelah tersedia.

Validasi ukuran dan jenis file. Jangan menerima file berbahaya atau memberi akses file privat kepada pengguna yang tidak berwenang.

## 8.5 Ujian

Murid dapat:
- Melihat ujian yang tersedia.
- Memulai ujian dalam periode yang diizinkan.
- Menjawab soal.
- Menyelesaikan dan mengirim jawaban.
- Melihat hasil sesuai kebijakan publikasi nilai.

Validasi waktu ujian di server. Jangan hanya mengandalkan timer JavaScript.

## 8.6 Nilai dan Absensi

Murid dapat melihat:
- Nilai tugas.
- Feedback guru.
- Hasil ujian.
- Riwayat kehadiran.
- Ringkasan perkembangan belajar.

Murid tidak dapat mengubah nilai atau absensinya sendiri.

---

# 9. DATABASE DAN ERD

Gunakan ERD yang sudah disiapkan sebagai acuan utama. Periksa migration dan model yang sudah tersedia sebelum membuat perubahan.

Entitas utama:

1. `users`
2. `student_profiles`
3. `teacher_profiles`
4. `programs`
5. `classes`
6. `enrollments`
7. `schedules`
8. `materials`
9. `assignments`
10. `submissions`
11. `assignment_grades`
12. `exams`
13. `exam_questions`
14. `exam_results`
15. `attendances`
16. `payments`
17. `notifications`
18. `chats`
19. `messages`

Tambahkan tabel jawaban ujian jika diperlukan untuk menyimpan jawaban per soal secara terstruktur.

## Relasi utama

- User memiliki satu profil murid atau satu profil guru sesuai role.
- Program memiliki banyak kelas.
- Guru dapat mengampu banyak kelas.
- Murid memiliki banyak pendaftaran.
- Kelas memiliki banyak pendaftaran.
- Kelas memiliki banyak jadwal.
- Kelas memiliki banyak materi.
- Guru dapat membuat banyak materi dan tugas.
- Tugas memiliki banyak pengumpulan.
- Pengumpulan dapat memiliki hasil penilaian.
- Kelas memiliki banyak ujian.
- Ujian memiliki banyak soal dan hasil pengerjaan.
- Jadwal memiliki banyak catatan absensi.
- Pendaftaran dapat memiliki satu atau lebih transaksi pembayaran sesuai aturan bisnis.
- User memiliki banyak notifikasi.
- Chat menghubungkan peserta percakapan yang berwenang.
- Chat memiliki banyak pesan.

Terapkan foreign key, unique constraint, index, tipe data yang sesuai, dan kebijakan penghapusan yang menjaga integritas riwayat.

Gunakan decimal untuk nilai uang dan hash untuk password. Jangan menyimpan informasi rahasia secara tidak aman.

---

# 10. ALUR STATUS DAN ATURAN BISNIS

## 10.1 Status pendaftaran

Gunakan status yang konsisten, misalnya:
- Pending payment.
- Waiting verification.
- Approved.
- Rejected.
- Cancelled.

Nama status boleh disesuaikan dengan konvensi project, tetapi maknanya harus konsisten di seluruh aplikasi.

## 10.2 Status pembayaran

Gunakan status seperti:
- Pending.
- Waiting verification.
- Paid.
- Rejected.
- Refunded jika fitur pengembalian dana memang diimplementasikan.

Jangan mencampur status pembayaran dengan status pendaftaran.

## 10.3 Akses kelas

Murid baru memperoleh akses pembelajaran setelah syarat pendaftaran terpenuhi sesuai kebijakan sistem.

Akses harus diperiksa di backend pada setiap request yang memerlukan otorisasi.

## 10.4 Perubahan data

- Perubahan pembayaran harus dibatasi untuk pihak berwenang.
- Nilai dan absensi hanya dapat diubah oleh guru yang berwenang atau admin sesuai kebijakan.
- Perubahan jadwal harus divalidasi.
- Kapasitas kelas tidak boleh dilampaui.
- Data historis harus tetap konsisten.
- Operasi yang melibatkan beberapa perubahan terkait harus menggunakan transaksi database.

---

# 11. KEAMANAN

Wajib menerapkan:
- Password hashing.
- CSRF protection.
- Validasi server-side.
- Otorisasi berdasarkan role dan kepemilikan data.
- Pencegahan mass assignment.
- Pembatasan percobaan login sesuai kebutuhan.
- Validasi upload file.
- Perlindungan file privat.
- Penanganan error yang tidak membocorkan informasi sensitif.
- Pengelolaan session secara aman.
- Pemeriksaan akses pada route, controller, dan policy yang relevan.

Jangan mengandalkan pembatasan tampilan frontend sebagai satu-satunya mekanisme keamanan.

---

# 12. NOTIFIKASI DAN CHAT

## 12.1 Notifikasi

Notifikasi dapat diberikan untuk:
- Pendaftaran berhasil dibuat.
- Pembayaran diverifikasi atau ditolak.
- Jadwal berubah.
- Materi baru tersedia.
- Tugas baru diterbitkan.
- Tenggat tugas mendekat jika mekanisme pengingat tersedia.
- Nilai dipublikasikan.
- Pengumuman kelas tersedia.

Notifikasi harus terkait dengan pengguna penerima yang benar.

## 12.2 Chat

Fitur chat mendukung komunikasi antara murid dan guru yang berwenang.

Fitur dasar:
- Membuat atau membuka percakapan.
- Mengirim pesan teks.
- Melihat riwayat pesan.
- Menampilkan waktu pengiriman.
- Menandai pesan dibaca jika diimplementasikan.

Jangan memberikan akses kepada pengguna yang bukan peserta percakapan.

---

# 13. FITUR DI LUAR RUANG LINGKUP AWAL

Jangan langsung mengimplementasikan fitur berikut sebelum fitur inti selesai:
- Payment gateway otomatis.
- Video conference terintegrasi.
- Aplikasi mobile native.
- Sistem AI tutor.
- Integrasi WhatsApp API otomatis.
- Gamifikasi kompleks.
- Multi-cabang dengan pengelolaan organisasi yang kompleks.

Fitur tambahan dapat dipertimbangkan setelah sistem inti stabil.

---

# 14. ACCEPTANCE CRITERIA

Sistem dinyatakan memenuhi kebutuhan minimum apabila:

1. Pengunjung dapat membuka halaman publik dan melihat program aktif.
2. Murid dapat membuat akun dan login dengan aman.
3. Pengguna diarahkan ke dashboard sesuai role.
4. Admin dapat mengelola program, kelas, guru, murid, dan jadwal.
5. Murid dapat mendaftar pada kelas yang tersedia.
6. Sistem memvalidasi kapasitas dan mencegah pendaftaran ganda.
7. Admin dapat memverifikasi pembayaran.
8. Status pendaftaran dan pembayaran tersimpan dengan konsisten.
9. Murid yang memenuhi syarat dapat mengakses kelas.
10. Guru dapat mengelola materi dan tugas untuk kelas yang diampu.
11. Murid dapat mengumpulkan tugas.
12. Guru dapat memberikan nilai dan feedback.
13. Guru dapat membuat ujian dan memeriksa hasilnya.
14. Guru dapat mencatat absensi.
15. Murid dapat melihat nilai dan riwayat absensi miliknya.
16. Notifikasi dikirim kepada penerima yang sesuai.
17. Akses chat dibatasi pada peserta yang berwenang.
18. Semua formulir penting memiliki validasi.
19. Halaman responsif di desktop dan mobile.
20. Fitur utama telah diuji dan tidak hanya menggunakan data dummy.

---

# 15. STRATEGI IMPLEMENTASI UNTUK CLAUDE CODE

Implementasikan project secara berurutan:

### Fase 1 — Audit
Periksa struktur project, versi Laravel, migration, model, route, controller, view, autentikasi, dan database yang sudah tersedia.

### Fase 2 — Fondasi database
Lengkapi migration, model, relasi, constraint, dan seeder yang diperlukan. Jangan menghapus tabel atau data yang sudah ada.

### Fase 3 — Autentikasi dan otorisasi
Selesaikan login, registrasi murid, role, middleware, dan hak akses.

### Fase 4 — Website publik
Selesaikan Home, Program, Detail Program, Jadwal, Tentang Kami, Kontak, Login, dan Register.

### Fase 5 — Admin
Selesaikan dashboard, manajemen pengguna, program, kelas, jadwal, pendaftaran, pembayaran, dan laporan.

### Fase 6 — Guru
Selesaikan dashboard guru, materi, tugas, penilaian, ujian, absensi, dan komunikasi.

### Fase 7 — Murid
Selesaikan dashboard murid, pendaftaran, pembayaran, akses kelas, materi, tugas, ujian, nilai, absensi, notifikasi, dan chat.

### Fase 8 — Integrasi dan pengujian
Hubungkan seluruh form dan tombol dengan backend. Uji validasi, hak akses, alur bisnis, relasi database, responsivitas, dan error handling.

---

# 16. INSTRUKSI EKSEKUSI UNTUK CLAUDE CODE

Gunakan PRD ini sebagai spesifikasi utama pengembangan Bimbel Sentra Edukasi.

Aturan wajib:

1. Periksa project yang sedang terbuka sebelum melakukan perubahan.
2. Jangan membuat ulang project Laravel.
3. Jangan mengulang konfigurasi database yang sudah selesai.
4. Jangan menjalankan `migrate:fresh` atau `db:wipe`.
5. Jangan menghapus data atau menimpa kode yang masih digunakan.
6. Jangan membuat file duplikat.
7. Jangan menganggap fitur selesai hanya karena halaman tampil.
8. Jangan menggunakan data dummy sebagai pengganti backend yang seharusnya sudah aktif.
9. Jangan membuat fitur di luar ruang lingkup tanpa kebutuhan yang jelas.
10. Pertahankan desain dan struktur project yang sudah ada jika masih layak digunakan.

Untuk setiap fase:
- Identifikasi file yang perlu dibuat atau diubah.
- Implementasikan kode yang lengkap dan terintegrasi.
- Jalankan pemeriksaan atau pengujian yang relevan.
- Perbaiki error yang ditemukan.
- Laporkan hasil nyata dan pekerjaan yang masih tersisa.

Mulai dari Fase 1. Setelah audit, lanjutkan ke fase berikutnya secara berurutan dan gunakan checkpoint agar perubahan tetap terkendali.

**Prioritas utama:** aplikasi berfungsi nyata, database konsisten, hak akses aman, alur bisnis benar, UI/UX responsif, dan seluruh implementasi sesuai Use Case serta ERD Bimbel Sentra Edukasi.

Jangan hanya menghasilkan rencana atau contoh kode. Jika akses project tersedia, kerjakan implementasinya langsung dengan tetap menjaga keamanan dan integritas kode yang sudah ada.