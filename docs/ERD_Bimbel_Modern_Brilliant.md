# ERD — Bimbel Sentra Edukasi
**Versi:** 2.0  
**Tanggal:** Oktober 2026  
**Perubahan dari v1.0:** Fitur ujian dihapus, `tutor_attendances` ditambahkan.

---

## Daftar Tabel

| # | Tabel | Keterangan |
|---|-------|-----------|
| 1 | `users` | Akun pengguna (admin, guru, murid) |
| 2 | `student_profiles` | Profil tambahan murid |
| 3 | `teacher_profiles` | Profil tambahan guru/tentor |
| 4 | `programs` | Program bimbel yang ditawarkan |
| 5 | `classes` | Kelas dalam suatu program |
| 6 | `schedules` | Jadwal sesi kelas |
| 7 | `enrollments` | Pendaftaran murid ke kelas |
| 8 | `payments` | Transaksi pembayaran per pendaftaran |
| 9 | `materials` | Materi pembelajaran per kelas |
| 10 | `assignments` | Tugas yang dibuat guru |
| 11 | `submissions` | Pengumpulan tugas oleh murid |
| 12 | `assignment_grades` | Penilaian dan feedback per pengumpulan |
| 13 | `attendances` | Absensi **murid** per jadwal |
| 14 | `tutor_attendances` | Check-in/check-out **guru** per jadwal |
| 15 | `notifications` | Notifikasi per pengguna |
| 16 | `chats` | Sesi percakapan murid ↔ guru |
| 17 | `messages` | Pesan dalam percakapan |

> **Dihapus dari scope:** `exams`, `exam_questions`, `exam_results`, `exam_answers`

---

## Data Dictionary

### 1. `users`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| `name` | VARCHAR(255) | NOT NULL | Nama lengkap |
| `email` | VARCHAR(255) | NOT NULL, UNIQUE | |
| `password` | VARCHAR(255) | NOT NULL | Bcrypt hash |
| `role` | ENUM | NOT NULL | `admin`, `guru`, `murid` |
| `is_active` | BOOLEAN | NOT NULL, DEFAULT true | Status akun |
| `email_verified_at` | TIMESTAMP | NULLABLE | |
| `remember_token` | VARCHAR(100) | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 2. `student_profiles`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `user_id` | BIGINT UNSIGNED | FK → users.id, UNIQUE | Satu akun, satu profil |
| `nis` | VARCHAR(50) | NULLABLE, UNIQUE | Nomor Induk Siswa |
| `phone` | VARCHAR(20) | NULLABLE | |
| `birth_date` | DATE | NULLABLE | |
| `address` | TEXT | NULLABLE | |
| `school` | VARCHAR(255) | NULLABLE | Asal sekolah |
| `grade_level` | VARCHAR(50) | NULLABLE | Kelas/tingkat |
| `parent_name` | VARCHAR(255) | NULLABLE | |
| `parent_phone` | VARCHAR(20) | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 3. `teacher_profiles`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `user_id` | BIGINT UNSIGNED | FK → users.id, UNIQUE | |
| `nip` | VARCHAR(50) | NULLABLE, UNIQUE | Nomor Induk Pegawai |
| `phone` | VARCHAR(20) | NULLABLE | |
| `subject_expertise` | VARCHAR(255) | NULLABLE | Mata pelajaran |
| `bio` | TEXT | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 4. `programs`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `name` | VARCHAR(255) | NOT NULL | Nama program |
| `category` | ENUM | NOT NULL | `akademik`, `non_akademik`, `bahasa`, `lainnya` |
| `level` | ENUM | NOT NULL | `SD`, `SMP`, `SMA`, `Umum` |
| `description` | TEXT | NULLABLE | |
| `price` | DECIMAL(10,2) | NOT NULL | Harga per periode |
| `duration_months` | TINYINT | NOT NULL | Durasi program (bulan) |
| `is_active` | BOOLEAN | NOT NULL, DEFAULT true | |
| `is_public` | BOOLEAN | NOT NULL, DEFAULT true | Tampil di halaman publik |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 5. `classes`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `program_id` | BIGINT UNSIGNED | FK → programs.id | |
| `teacher_id` | BIGINT UNSIGNED | FK → users.id | Guru pengampu (role=guru) |
| `name` | VARCHAR(255) | NOT NULL | Nama kelas |
| `capacity` | TINYINT | NOT NULL | Kapasitas maksimum |
| `mode` | ENUM | NOT NULL | `online`, `offline`, `hybrid` |
| `status` | ENUM | NOT NULL, DEFAULT `active` | `active`, `inactive`, `full` |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 6. `schedules`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `class_id` | BIGINT UNSIGNED | FK → classes.id | |
| `day_of_week` | ENUM | NOT NULL | `Senin`…`Minggu` |
| `start_time` | TIME | NOT NULL | |
| `end_time` | TIME | NOT NULL | |
| `room` | VARCHAR(100) | NULLABLE | Ruangan / link online |
| `status` | ENUM | NOT NULL, DEFAULT `active` | `active`, `cancelled` |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 7. `enrollments`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `student_id` | BIGINT UNSIGNED | FK → users.id | Murid (role=murid) |
| `class_id` | BIGINT UNSIGNED | FK → classes.id | |
| `status` | ENUM | NOT NULL, DEFAULT `pending_payment` | `pending_payment`, `waiting_verification`, `approved`, `rejected`, `cancelled` |
| `enrolled_at` | DATE | NULLABLE | Tanggal approved |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

**Constraint:** UNIQUE(`student_id`, `class_id`) — mencegah pendaftaran ganda.

---

### 8. `payments`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `enrollment_id` | BIGINT UNSIGNED | FK → enrollments.id | |
| `amount` | DECIMAL(10,2) | NOT NULL | Nominal pembayaran |
| `status` | ENUM | NOT NULL, DEFAULT `pending` | `pending`, `waiting_verification`, `paid`, `rejected`, `refunded` |
| `payment_method` | VARCHAR(100) | NULLABLE | Transfer, tunai, dll. |
| `proof_file` | VARCHAR(500) | NULLABLE | Path file bukti bayar |
| `rejection_note` | TEXT | NULLABLE | Alasan penolakan |
| `verified_by` | BIGINT UNSIGNED | FK → users.id, NULLABLE | Admin verifikator |
| `verified_at` | TIMESTAMP | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 9. `materials`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `class_id` | BIGINT UNSIGNED | FK → classes.id | |
| `teacher_id` | BIGINT UNSIGNED | FK → users.id | Pembuat materi |
| `title` | VARCHAR(255) | NOT NULL | |
| `description` | TEXT | NULLABLE | |
| `file_path` | VARCHAR(500) | NULLABLE | |
| `external_url` | VARCHAR(500) | NULLABLE | Tautan eksternal |
| `status` | ENUM | NOT NULL, DEFAULT `draft` | `draft`, `published` |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 10. `assignments`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `class_id` | BIGINT UNSIGNED | FK → classes.id | |
| `teacher_id` | BIGINT UNSIGNED | FK → users.id | |
| `title` | VARCHAR(255) | NOT NULL | |
| `instructions` | TEXT | NOT NULL | |
| `attachment_path` | VARCHAR(500) | NULLABLE | |
| `due_at` | TIMESTAMP | NOT NULL | Tenggat pengumpulan |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 11. `submissions`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `assignment_id` | BIGINT UNSIGNED | FK → assignments.id | |
| `student_id` | BIGINT UNSIGNED | FK → users.id | |
| `file_path` | VARCHAR(500) | NULLABLE | |
| `notes` | TEXT | NULLABLE | Catatan murid |
| `submitted_at` | TIMESTAMP | NOT NULL | Waktu pengumpulan (server) |
| `status` | ENUM | NOT NULL | `submitted`, `late`, `graded` |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

**Constraint:** UNIQUE(`assignment_id`, `student_id`) — satu pengumpulan per tugas per murid.

---

### 12. `assignment_grades`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `submission_id` | BIGINT UNSIGNED | FK → submissions.id, UNIQUE | |
| `teacher_id` | BIGINT UNSIGNED | FK → users.id | |
| `score` | DECIMAL(5,2) | NOT NULL | 0.00 – 100.00 |
| `feedback` | TEXT | NULLABLE | |
| `graded_at` | TIMESTAMP | NOT NULL | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 13. `attendances` _(Absensi Murid)_

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `schedule_id` | BIGINT UNSIGNED | FK → schedules.id | |
| `student_id` | BIGINT UNSIGNED | FK → users.id | |
| `status` | ENUM | NOT NULL | `hadir`, `izin`, `sakit`, `alpa` |
| `notes` | TEXT | NULLABLE | |
| `attendance_date` | DATE | NOT NULL | Tanggal sesi |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

**Constraint:** UNIQUE(`schedule_id`, `student_id`, `attendance_date`) — mencegah duplikasi absensi.

---

### 14. `tutor_attendances` _(Check-in/Check-out Guru)_

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `teacher_id` | BIGINT UNSIGNED | FK → users.id | Guru pengampu |
| `schedule_id` | BIGINT UNSIGNED | FK → schedules.id | Jadwal yang relevan |
| `check_in_at` | TIMESTAMP | NULLABLE | Dicatat server saat check-in |
| `check_out_at` | TIMESTAMP | NULLABLE | Dicatat server saat check-out |
| `status` | ENUM | NOT NULL, DEFAULT `incomplete` | `on_time`, `late`, `incomplete`, `completed` |
| `notes` | TEXT | NULLABLE | Catatan tambahan |
| `session_date` | DATE | NOT NULL | Tanggal sesi |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

**Constraint:** UNIQUE(`teacher_id`, `schedule_id`, `session_date`) — mencegah duplikasi per sesi.

**Aturan bisnis:**
- Guru hanya dapat check-in untuk jadwal yang terhubung ke kelas yang diampunya.
- Check-out hanya diizinkan jika `check_in_at` sudah terisi.
- Waktu ditetapkan oleh server (`now()`), tidak dari input browser.
- Status `late` ditentukan dengan membandingkan `check_in_at` terhadap `schedules.start_time`.
- Status `incomplete` jika sesi berakhir tetapi `check_out_at` masih null.
- Riwayat disimpan permanen untuk kebutuhan laporan.

---

### 15. `notifications`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `user_id` | BIGINT UNSIGNED | FK → users.id | Penerima |
| `title` | VARCHAR(255) | NOT NULL | |
| `body` | TEXT | NOT NULL | |
| `type` | VARCHAR(100) | NULLABLE | Kategori notifikasi |
| `is_read` | BOOLEAN | NOT NULL, DEFAULT false | |
| `read_at` | TIMESTAMP | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

### 16. `chats`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `student_id` | BIGINT UNSIGNED | FK → users.id | |
| `teacher_id` | BIGINT UNSIGNED | FK → users.id | |
| `last_message_at` | TIMESTAMP | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

**Constraint:** UNIQUE(`student_id`, `teacher_id`) — satu saluran per pasangan.

---

### 17. `messages`

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|-----------|
| `id` | BIGINT UNSIGNED | PK | |
| `chat_id` | BIGINT UNSIGNED | FK → chats.id | |
| `sender_id` | BIGINT UNSIGNED | FK → users.id | |
| `body` | TEXT | NOT NULL | |
| `is_read` | BOOLEAN | NOT NULL, DEFAULT false | |
| `read_at` | TIMESTAMP | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

## Relasi & Kardinalitas

| Entitas A | Kardinalitas | Entitas B | FK / Catatan |
|-----------|-------------|-----------|-------------|
| users | 1 : 0..1 | student_profiles | `student_profiles.user_id` |
| users | 1 : 0..1 | teacher_profiles | `teacher_profiles.user_id` |
| programs | 1 : N | classes | `classes.program_id` |
| users (guru) | 1 : N | classes | `classes.teacher_id` |
| classes | 1 : N | schedules | `schedules.class_id` |
| classes | 1 : N | enrollments | `enrollments.class_id` |
| users (murid) | 1 : N | enrollments | `enrollments.student_id` |
| enrollments | 1 : N | payments | `payments.enrollment_id` |
| users (admin) | 1 : N | payments | `payments.verified_by` |
| classes | 1 : N | materials | `materials.class_id` |
| users (guru) | 1 : N | materials | `materials.teacher_id` |
| classes | 1 : N | assignments | `assignments.class_id` |
| users (guru) | 1 : N | assignments | `assignments.teacher_id` |
| assignments | 1 : N | submissions | `submissions.assignment_id` |
| users (murid) | 1 : N | submissions | `submissions.student_id` |
| submissions | 1 : 0..1 | assignment_grades | `assignment_grades.submission_id` |
| users (guru) | 1 : N | assignment_grades | `assignment_grades.teacher_id` |
| schedules | 1 : N | attendances | `attendances.schedule_id` |
| users (murid) | 1 : N | attendances | `attendances.student_id` |
| schedules | 1 : N | tutor_attendances | `tutor_attendances.schedule_id` |
| users (guru) | 1 : N | tutor_attendances | `tutor_attendances.teacher_id` |
| users | 1 : N | notifications | `notifications.user_id` |
| users (murid) | 1 : N | chats | `chats.student_id` |
| users (guru) | 1 : N | chats | `chats.teacher_id` |
| chats | 1 : N | messages | `messages.chat_id` |
| users | 1 : N | messages | `messages.sender_id` |

---

## Ringkasan Perubahan v1.0 → v2.0

### ❌ Dihapus
- `exams` — tabel ujian
- `exam_questions` — soal ujian
- `exam_results` — hasil pengerjaan ujian
- `exam_answers` — jawaban per soal

### ✅ Ditambahkan
- `tutor_attendances` — check-in/check-out guru/tentor per sesi jadwal
  - Mencatat `check_in_at` dan `check_out_at` dengan waktu server
  - Status: `on_time`, `late`, `incomplete`, `completed`
  - UNIQUE per `(teacher_id, schedule_id, session_date)`

### 🔒 Aturan Keamanan Baru
- Check-in hanya valid untuk jadwal yang menjadi tanggung jawab guru
- Waktu selalu dari server, tidak dipercaya dari input klien
- Duplikasi dicegah oleh unique constraint database

### 📋 Tetap Dipertahankan
Semua tabel selain yang dihapus di atas tetap dipertahankan tanpa perubahan struktur signifikan.
