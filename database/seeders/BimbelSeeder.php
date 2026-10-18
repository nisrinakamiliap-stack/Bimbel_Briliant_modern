<?php

namespace Database\Seeders;

use App\EnrollmentStatus;
use App\LearningContentStatus;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\PaymentMethod;
use App\PaymentStatus;
use App\Weekday;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data demo Bimbel Briliant Modern.
 *
 * Aman dijalankan setelah migrate:fresh. Jangan jalankan di production.
 * Semua password: password
 */
class BimbelSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. PROGRAM ────────────────────────────────────────────────
        $this->call(ProgramSeeder::class);

        $programs = Program::all()->keyBy('slug');
        // fallback jika ProgramSeeder tidak mengisi (config kosong)
        if ($programs->isEmpty()) {
            $programs = collect([
                'sd'  => Program::firstOrCreate(['slug' => 'sd'],  ['name' => 'Bimbel SD',  'level' => \App\ProgramLevel::Sd,  'price' => 350000, 'is_active' => true]),
                'smp' => Program::firstOrCreate(['slug' => 'smp'], ['name' => 'Bimbel SMP', 'level' => \App\ProgramLevel::Smp, 'price' => 450000, 'is_active' => true]),
                'sma' => Program::firstOrCreate(['slug' => 'sma'], ['name' => 'Bimbel SMA', 'level' => \App\ProgramLevel::Sma, 'price' => 550000, 'is_active' => true]),
            ]);
        }

        // ── 2. ADMIN ──────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@briliant.test'],
            ['role' => \App\UserRole::Admin, 'name' => 'Admin Briliant', 'email_verified_at' => now(), 'password' => Hash::make('password')],
        );

        // ── 3. GURU ───────────────────────────────────────────────────
        $teachers = collect([
            ['name' => 'Rina Kusumawati',  'email' => 'rina@briliant.test',   'subject' => 'Matematika'],
            ['name' => 'Budi Santoso',     'email' => 'budi@briliant.test',   'subject' => 'Bahasa Inggris'],
            ['name' => 'Dewi Anggraeni',   'email' => 'dewi@briliant.test',   'subject' => 'Bahasa Indonesia'],
        ])->map(function (array $data) {
            $teacher = User::firstOrCreate(
                ['email' => $data['email']],
                ['role' => \App\UserRole::Teacher, 'name' => $data['name'], 'email_verified_at' => now(), 'password' => Hash::make('password')],
            );
            if (! $teacher->teacherProfile()->exists()) {
                $teacher->teacherProfile()->create([
                    'subject'        => $data['subject'],
                    'last_education' => \App\EducationDegree::S1,
                    'institution'    => 'Universitas Negeri Jakarta',
                    'address'        => fake()->address(),
                    'bio'            => 'Pengajar berpengalaman di bidang '.$data['subject'].'.',
                ]);
            }
            return $teacher;
        });

        [$teacherRina, $teacherBudi, $teacherDewi] = [$teachers[0], $teachers[1], $teachers[2]];

        // ── 4. MURID ──────────────────────────────────────────────────
        $studentData = [
            ['name' => 'Aulia Rahma',     'email' => 'aulia@briliant.test',   'level' => \App\EducationLevel::Smp, 'school' => 'SMP Negeri 1 Jakarta'],
            ['name' => 'Fajar Nugroho',   'email' => 'fajar@briliant.test',   'level' => \App\EducationLevel::Smp, 'school' => 'SMP Negeri 3 Jakarta'],
            ['name' => 'Siti Nuraini',    'email' => 'siti@briliant.test',    'level' => \App\EducationLevel::Sma, 'school' => 'SMA Negeri 2 Jakarta'],
            ['name' => 'Rizky Pratama',   'email' => 'rizky@briliant.test',   'level' => \App\EducationLevel::Sma, 'school' => 'SMA Negeri 5 Jakarta'],
            ['name' => 'Nadhira Putri',   'email' => 'nadhira@briliant.test', 'level' => \App\EducationLevel::Sd,  'school' => 'SD Negeri Menteng 01'],
            ['name' => 'Kevin Alfarizi',  'email' => 'kevin@briliant.test',   'level' => \App\EducationLevel::Sd,  'school' => 'SD Negeri Kebayoran 03'],
            ['name' => 'Luthfi Hakim',    'email' => 'luthfi@briliant.test',  'level' => \App\EducationLevel::Smp, 'school' => 'SMP Negeri 7 Jakarta'],
            ['name' => 'Zahra Amelia',    'email' => 'zahra@briliant.test',   'level' => \App\EducationLevel::Sma, 'school' => 'SMA Negeri 8 Jakarta'],
        ];

        $students = collect($studentData)->map(function (array $data) {
            $student = User::firstOrCreate(
                ['email' => $data['email']],
                ['role' => \App\UserRole::Student, 'name' => $data['name'], 'email_verified_at' => now(), 'password' => Hash::make('password')],
            );
            if (! $student->studentProfile()->exists()) {
                $student->studentProfile()->create([
                    'education_level' => $data['level'],
                    'school_name'     => $data['school'],
                    'birth_date'      => fake()->dateTimeBetween('-17 years', '-8 years'),
                    'address'         => fake()->address(),
                    'parent_name'     => fake()->name(),
                    'parent_phone'    => '08'.fake()->numerify('##########'),
                ]);
            }
            return $student;
        });

        // ── 5. KELAS ──────────────────────────────────────────────────
        $classrooms = [
            'smp_mat' => Classroom::firstOrCreate(['code' => 'MAT-SMP-A'], [
                'program_id'    => $programs['smp']->id,
                'teacher_id'    => $teacherRina->id,
                'name'          => 'Matematika SMP — Kelas A',
                'capacity'      => 15,
                'learning_mode' => \App\LearningMode::Offline,
                'description'   => 'Kelas matematika intensif untuk murid SMP, fokus pada aljabar dan geometri.',
                'is_active'     => true,
            ]),
            'sma_eng' => Classroom::firstOrCreate(['code' => 'ENG-SMA-B'], [
                'program_id'    => $programs['sma']->id,
                'teacher_id'    => $teacherBudi->id,
                'name'          => 'Bahasa Inggris SMA — Kelas B',
                'capacity'      => 12,
                'learning_mode' => \App\LearningMode::Online,
                'description'   => 'Persiapan ujian nasional dan TOEFL untuk murid SMA.',
                'is_active'     => true,
            ]),
            'sd_indo' => Classroom::firstOrCreate(['code' => 'IND-SD-C'], [
                'program_id'    => $programs['sd']->id,
                'teacher_id'    => $teacherDewi->id,
                'name'          => 'Bahasa Indonesia SD — Kelas C',
                'capacity'      => 10,
                'learning_mode' => \App\LearningMode::Offline,
                'description'   => 'Meningkatkan kemampuan membaca, menulis, dan pemahaman bahasa Indonesia.',
                'is_active'     => true,
            ]),
        ];

        // ── 6. JADWAL ─────────────────────────────────────────────────
        // smp_mat: Selasa & Kamis 15:00–16:30
        Schedule::firstOrCreate(['classroom_id' => $classrooms['smp_mat']->id, 'day_of_week' => Weekday::Tuesday],  ['start_time' => '15:00', 'end_time' => '16:30', 'room' => 'Ruang 1', 'is_active' => true]);
        Schedule::firstOrCreate(['classroom_id' => $classrooms['smp_mat']->id, 'day_of_week' => Weekday::Thursday], ['start_time' => '15:00', 'end_time' => '16:30', 'room' => 'Ruang 1', 'is_active' => true]);

        // sma_eng: Senin & Rabu 16:00–17:30
        Schedule::firstOrCreate(['classroom_id' => $classrooms['sma_eng']->id, 'day_of_week' => Weekday::Monday],    ['start_time' => '16:00', 'end_time' => '17:30', 'room' => 'Online', 'is_active' => true]);
        Schedule::firstOrCreate(['classroom_id' => $classrooms['sma_eng']->id, 'day_of_week' => Weekday::Wednesday], ['start_time' => '16:00', 'end_time' => '17:30', 'room' => 'Online', 'is_active' => true]);

        // sd_indo: Sabtu 09:00–10:30
        Schedule::firstOrCreate(['classroom_id' => $classrooms['sd_indo']->id, 'day_of_week' => Weekday::Saturday], ['start_time' => '09:00', 'end_time' => '10:30', 'room' => 'Ruang 2', 'is_active' => true]);

        // ── 7. PENDAFTARAN & PEMBAYARAN ───────────────────────────────
        // [student_index, classroom_key, status, paid_months]
        $enrollmentMap = [
            [0, 'smp_mat', EnrollmentStatus::Approved, 2],  // Aulia  → SMP Mat
            [1, 'smp_mat', EnrollmentStatus::Approved, 1],  // Fajar  → SMP Mat
            [2, 'sma_eng', EnrollmentStatus::Approved, 2],  // Siti   → SMA Eng
            [3, 'sma_eng', EnrollmentStatus::Approved, 1],  // Rizky  → SMA Eng
            [4, 'sd_indo', EnrollmentStatus::Approved, 2],  // Nadhira → SD Indo
            [5, 'sd_indo', EnrollmentStatus::Approved, 0],  // Kevin  → SD Indo (belum bayar)
            [6, 'smp_mat', EnrollmentStatus::Pending,  0],  // Luthfi → menunggu
            [7, 'sma_eng', EnrollmentStatus::Approved, 1],  // Zahra  → SMA Eng
        ];

        foreach ($enrollmentMap as [$idx, $classKey, $status, $paidMonths]) {
            $classroom = $classrooms[$classKey];
            $student   = $students[$idx];
            $price     = $classroom->program->price ?? 400000;

            $enrollment = Enrollment::firstOrCreate(
                ['student_id' => $student->id, 'program_id' => $classroom->program_id],
                [
                    'classroom_id'      => $status === EnrollmentStatus::Approved ? $classroom->id : null,
                    'bill_amount'       => $price,
                    'enrolled_on'       => Carbon::now()->subMonths(3)->format('Y-m-d'),
                    'status'            => $status,
                    'status_changed_at' => $status !== EnrollmentStatus::Pending ? Carbon::now()->subMonths(3) : null,
                ],
            );

            $existingPayments = $enrollment->payments()->count();
            for ($m = $existingPayments; $m < $paidMonths; $m++) {
                Payment::create([
                    'enrollment_id' => $enrollment->id,
                    'amount'        => $price,
                    'method'        => $m === 0 ? PaymentMethod::BankTransfer : PaymentMethod::Cash,
                    'paid_on'       => Carbon::now()->subMonths($paidMonths - $m)->format('Y-m-d'),
                    'status'        => PaymentStatus::Paid,
                    'processed_by'  => $admin->id,
                    'processed_at'  => Carbon::now()->subMonths($paidMonths - $m)->addDays(1),
                    'notes'         => 'Pembayaran bulan ke-'.($m + 1),
                ]);
            }
        }

        // ── 8. MATERI ─────────────────────────────────────────────────
        $materialsData = [
            ['classroom' => 'smp_mat', 'items' => [
                ['title' => 'Persamaan Linear Satu Variabel',   'published_at' => now()->subWeeks(6)],
                ['title' => 'Sistem Persamaan Linear Dua Variabel', 'published_at' => now()->subWeeks(4)],
                ['title' => 'Pertidaksamaan Linear',            'published_at' => now()->subWeeks(2)],
                ['title' => 'Fungsi dan Grafik',                'published_at' => now()->subDays(5)],
                ['title' => 'Pythagoras dan Trigonometri Dasar','published_at' => null, 'status' => LearningContentStatus::Draft],
            ]],
            ['classroom' => 'sma_eng', 'items' => [
                ['title' => 'Grammar: Simple Past vs Past Continuous', 'published_at' => now()->subWeeks(5)],
                ['title' => 'Reading Comprehension Strategies',        'published_at' => now()->subWeeks(3)],
                ['title' => 'Essay Writing — Argumentative Text',      'published_at' => now()->subWeeks(1)],
                ['title' => 'Vocabulary Building for TOEFL',           'published_at' => now()->subDays(3)],
            ]],
            ['classroom' => 'sd_indo', 'items' => [
                ['title' => 'Membaca Nyaring dan Pemahaman Bacaan', 'published_at' => now()->subWeeks(4)],
                ['title' => 'Menulis Karangan Sederhana',           'published_at' => now()->subWeeks(2)],
                ['title' => 'Kata Baku dan Tidak Baku',             'published_at' => now()->subDays(7)],
            ]],
        ];

        foreach ($materialsData as $group) {
            $classroom = $classrooms[$group['classroom']];
            foreach ($group['items'] as $item) {
                Material::firstOrCreate(
                    ['classroom_id' => $classroom->id, 'title' => $item['title']],
                    [
                        'description'  => 'Materi pembelajaran: '.$item['title'].'.',
                        'content'      => '<p>Ini adalah konten materi <strong>'.$item['title'].'</strong>. Silakan tambahkan isi materi melalui panel admin atau portal guru.</p>',
                        'status'       => $item['status'] ?? LearningContentStatus::Published,
                        'published_at' => $item['published_at'] ?? null,
                    ],
                );
            }
        }

        // ── 9. ABSENSI MURID & CHECK-IN GURU (4 minggu terakhir) ─────
        $approvedEnrollments = Enrollment::with(['classroom.schedules'])
            ->where('status', EnrollmentStatus::Approved)
            ->whereNotNull('classroom_id')
            ->get();

        $attendanceStatuses = [
            \App\AttendanceStatus::Present,
            \App\AttendanceStatus::Present,
            \App\AttendanceStatus::Present,
            \App\AttendanceStatus::Sick,
            \App\AttendanceStatus::Absent,
        ];

        $now = CarbonImmutable::now();

        foreach ($approvedEnrollments as $enrollment) {
            foreach ($enrollment->classroom->schedules as $schedule) {
                $sessionDate = $now->startOfDay();
                $sessions = 0;
                while ($sessions < 4) {
                    $sessionDate = $sessionDate->subDay();
                    if ($sessionDate->isoWeekday() !== $schedule->day_of_week->value) {
                        continue;
                    }
                    $sessions++;
                    $dateStr = $sessionDate->format('Y-m-d');

                    $alreadyExists = StudentAttendance::where('student_id', $enrollment->student_id)
                        ->where('schedule_id', $schedule->id)
                        ->where('session_date', $dateStr)
                        ->exists();

                    if (! $alreadyExists) {
                        StudentAttendance::factory()
                            ->forSession($schedule, $enrollment, $dateStr)
                            ->create(['status' => fake()->randomElement($attendanceStatuses)]);
                    }
                }
            }
        }

        // Check-in guru untuk sesi yang sama
        foreach ($classrooms as $classroom) {
            $teacher = $classroom->teacher;
            if (! $teacher) {
                continue;
            }
            foreach ($classroom->schedules as $schedule) {
                $sessionDate = $now->startOfDay();
                $sessions = 0;
                while ($sessions < 4) {
                    $sessionDate = $sessionDate->subDay();
                    if ($sessionDate->isoWeekday() !== $schedule->day_of_week->value) {
                        continue;
                    }
                    $sessions++;

                    [$startH, $startM] = explode(':', $schedule->start_time);
                    [$endH, $endM]     = explode(':', $schedule->end_time);

                    $checkedIn  = $sessionDate->setTime((int) $startH, (int) $startM)->addMinutes(fake()->numberBetween(-5, 10));
                    $checkedOut = $sessionDate->setTime((int) $endH,   (int) $endM)->addMinutes(fake()->numberBetween(0, 15));

                    $exists = TeacherAttendance::where('teacher_id', $teacher->id)
                        ->where('schedule_id', $schedule->id)
                        ->where('session_date', $sessionDate->format('Y-m-d'))
                        ->exists();

                    if (! $exists) {
                        $ta = new TeacherAttendance();
                        $ta->teacher_id     = $teacher->id;
                        $ta->schedule_id    = $schedule->id;
                        $ta->session_date   = $sessionDate->format('Y-m-d');
                        $ta->checked_in_at  = $checkedIn;
                        $ta->checked_out_at = $checkedOut;
                        $ta->status         = \App\TeacherAttendanceStatus::Present;
                        $ta->save();
                    }
                }
            }
        }

        $this->command->info('BimbelSeeder selesai.');
        $this->command->table(
            ['Role', 'Nama', 'Email', 'Password'],
            [
                ['Admin', 'Admin Briliant', 'admin@briliant.test', 'password'],
                ['Guru', 'Rina Kusumawati', 'rina@briliant.test', 'password'],
                ['Guru', 'Budi Santoso', 'budi@briliant.test', 'password'],
                ['Guru', 'Dewi Anggraeni', 'dewi@briliant.test', 'password'],
                ['Murid', 'Aulia Rahma', 'aulia@briliant.test', 'password'],
                ['Murid', 'Fajar Nugroho', 'fajar@briliant.test', 'password'],
                ['Murid', 'Siti Nuraini', 'siti@briliant.test', 'password'],
                ['Murid', 'Rizky Pratama', 'rizky@briliant.test', 'password'],
                ['Murid', 'Nadhira Putri', 'nadhira@briliant.test', 'password'],
                ['Murid', 'Kevin Alfarizi', 'kevin@briliant.test', 'password'],
                ['Murid', 'Luthfi Hakim (pending)', 'luthfi@briliant.test', 'password'],
                ['Murid', 'Zahra Amelia', 'zahra@briliant.test', 'password'],
            ]
        );
    }
}
