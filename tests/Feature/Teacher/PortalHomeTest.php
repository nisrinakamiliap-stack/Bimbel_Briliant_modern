<?php

namespace Tests\Feature\Teacher;

use App\EnrollmentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\Announcement;
use App\Weekday;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalHomeTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-19 09:00:00'));

        $this->teacher = User::factory()->teacher()->create(['name' => 'Budi Santoso']);
        $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id, 'name' => 'SMA 12 IPA A']);
    }

    /**
     * Test guru yang login diarahkan ke Beranda Portal Guru.
     */
    public function test_teacher_dashboard_route_redirects_to_portal_home(): void
    {
        $this->actingAs($this->teacher)->get(route('dashboard'))->assertRedirect(route('teacher.dashboard'));
        $this->actingAs($this->teacher)->get('/guru')->assertRedirect('/guru/dashboard');
    }

    /**
     * Test beranda menampilkan sapaan, tanggal, dan ilustrasi guru.png dari folder public.
     */
    public function test_home_shows_greeting_date_and_teacher_illustration(): void
    {
        $this->assertFileExists(public_path('images/guru.png'));

        $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Selamat pagi, Budi')
            ->assertSee('Senin, 19 Oktober 2026')
            ->assertSee(asset('images/guru.png'), false)
            ->assertSee('Portal Guru');
    }

    /**
     * Test navbar memuat seluruh menu Portal Guru dengan tautan yang benar dan tanpa menu admin.
     */
    public function test_navbar_lists_every_menu_with_working_links(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('teacher.dashboard'))->assertOk();

        $response->assertSeeInOrder(['Beranda', 'Kelas &amp; Jadwal', 'Pembelajaran', 'Absensi'], false);
        $response->assertSeeInOrder(['Materi Pembelajaran', 'Tugas', 'Pengumpulan Tugas', 'Penilaian Tugas']);
        $this->assertDoesNotMatchRegularExpression('/class="teacher-nav-link[^"]*"[^>]*>\s*Pesan/', $response->getContent());

        foreach (['teacher.dashboard', 'teacher.schedules.index', 'teacher.classes.index', 'teacher.learning.materials.index', 'teacher.learning.assignments.index', 'teacher.learning.submissions.index', 'teacher.learning.grades.index', 'teacher.attendances.index', 'messages.index', 'notifications.index', 'teacher.profile.edit'] as $routeName) {
            $response->assertSee('href="'.route($routeName).'"', false);
            $this->actingAs($this->teacher)->get(route($routeName))->assertOk();
        }

        $response->assertSee(route('logout'), false);
        $response->assertDontSee(route('admin.dashboard'), false);
        $response->assertDontSee('admin-sidebar', false);
    }

    /**
     * Test menu aktif mengikuti route yang sedang dibuka.
     */
    public function test_active_menu_follows_current_route(): void
    {
        $menuPattern = '/href="'.preg_quote(route('teacher.schedules.index'), '/').'" class="teacher-nav-link is-active"\s+aria-current="page"/';

        $content = $this->actingAs($this->teacher)->get(route('teacher.schedules.index'))->getContent();
        $this->assertMatchesRegularExpression($menuPattern, $content);

        $content = $this->actingAs($this->teacher)->get(route('teacher.classes.index'))->getContent();
        $this->assertMatchesRegularExpression($menuPattern, $content);
        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('teacher.classes.index'), '/').'" class="is-active"\s+aria-current="page"\s*>Kelas Saya</', $content);

        $content = $this->actingAs($this->teacher)->get(route('teacher.learning.assignments.index'))->getContent();
        $this->assertMatchesRegularExpression('/id="nav-learning-toggle" class="teacher-nav-link is-active"/', $content);
    }

    /**
     * Test menu Kelas & Jadwal menyediakan tab ke Jadwal Mengajar dan Kelas Saya.
     */
    public function test_class_and_schedule_menu_has_both_tabs(): void
    {
        foreach (['teacher.schedules.index', 'teacher.classes.index'] as $routeName) {
            $this->actingAs($this->teacher)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee('aria-label="Bagian Kelas &amp; Jadwal"', false)
                ->assertSee('href="'.route('teacher.schedules.index').'"', false)
                ->assertSee('href="'.route('teacher.classes.index').'"', false);
        }
    }

    /**
     * Test tombol Pesan mengambang mengarah ke halaman Pesan, menampilkan jumlah belum dibaca,
     * dan tidak tampil di halaman Pesan itu sendiri.
     */
    public function test_floating_message_button(): void
    {
        $student = User::factory()->student()->create();
        Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create(['student_id' => $student->id]);
        $this->actingAs($student)->post(route('messages.store'), ['recipient_id' => $this->teacher->id, 'body' => 'Pak, besok ada kelas?']);

        $content = $this->actingAs($this->teacher)->get(route('teacher.dashboard'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<a href="'.preg_quote(route('messages.index'), '/').'" id="teacher-message-button"/', $content);
        $this->assertStringContainsString('aria-label="Pesan, 1 percakapan belum dibaca"', $content);

        $this->actingAs($this->teacher)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertDontSee('id="teacher-message-button"', false)
            ->assertSee('Pak, besok ada kelas?');
    }

    /**
     * Test jadwal hari ini hanya berisi sesi milik guru yang login, beserta tombol check-in saat jendela terbuka.
     */
    public function test_today_sessions_belong_to_logged_in_teacher_only(): void
    {
        $schedule = Schedule::factory()->for($this->classroom)->at(Weekday::Monday, '09:15', '10:45')->create(['room' => 'Ruang Merapi']);
        $otherClassroom = Classroom::factory()->create(['name' => 'Kelas Guru Lain']);
        Schedule::factory()->for($otherClassroom)->at(Weekday::Monday, '09:00', '10:00')->create();
        Schedule::factory()->for($this->classroom)->at(Weekday::Tuesday, '09:00', '10:00')->create();

        $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('SMA 12 IPA A')
            ->assertSee('Ruang Merapi')
            ->assertSee('09:15')
            ->assertSee('Akan Datang')
            ->assertSee(route('teacher.attendance.check-in', $schedule), false)
            ->assertDontSee('Kelas Guru Lain');
    }

    /**
     * Test empty state jadwal hari ini menyebutkan sesi berikutnya dari data sebenarnya.
     */
    public function test_empty_today_shows_next_real_session(): void
    {
        Schedule::factory()->for($this->classroom)->at(Weekday::Wednesday, '13:00', '14:30')->create();

        $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Tidak ada kelas hari ini')
            ->assertSee('Rabu, 21 Oktober 2026 pukul 13:00');
    }

    /**
     * Test aktivitas menampilkan pengumpulan belum dinilai dari kelas sendiri dan notifikasi milik guru.
     */
    public function test_activity_shows_real_ungraded_submissions_and_notifications(): void
    {
        $enrollment = Enrollment::factory()->inClassroom($this->classroom)->withStatus(EnrollmentStatus::Approved)->create();
        $enrollment->student->update(['name' => 'Dewi Lestari']);
        $assignment = Assignment::factory()->for($this->classroom)->create(['title' => 'Latihan Integral']);
        Submission::factory()->for($assignment)->byEnrollment($enrollment)->create();

        $otherAssignment = Assignment::factory()->create(['title' => 'Tugas Kelas Lain']);
        Submission::factory()->for($otherAssignment)->create();

        $this->teacher->notify(new Announcement('libur-1', 'Libur Nasional', 'Bimbel libur hari Kamis.'));

        $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertSee('Latihan Integral')
            ->assertSee('1 pengumpulan belum diberi nilai.')
            ->assertDontSee('Tugas Kelas Lain')
            ->assertSee('Libur Nasional');
    }

    /**
     * Test Pesan dan Notifikasi untuk guru memakai layout Portal Guru.
     */
    public function test_messages_and_notifications_use_teacher_layout(): void
    {
        foreach (['messages.index', 'messages.create', 'notifications.index'] as $routeName) {
            $this->actingAs($this->teacher)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee('teacher-body', false)
                ->assertSee('Portal Guru');
        }
    }

    /**
     * Test admin dan murid tidak dapat membuka halaman Portal Guru, dan tamu diarahkan ke login.
     */
    public function test_only_teachers_can_open_the_portal(): void
    {
        $pages = ['teacher.dashboard', 'teacher.schedules.index', 'teacher.classes.index', 'teacher.learning.materials.index', 'teacher.learning.assignments.create', 'teacher.learning.submissions.index', 'teacher.learning.grades.index', 'teacher.attendances.index', 'teacher.attendances.create', 'teacher.profile.edit'];

        foreach ([User::factory()->admin()->create(), User::factory()->student()->create()] as $user) {
            foreach ($pages as $routeName) {
                $this->actingAs($user)->get(route($routeName))->assertForbidden();
            }
        }

        auth()->logout();
        $this->get(route('teacher.dashboard'))->assertRedirect(route('login'));
    }

    /**
     * Test guru tidak dapat membuka halaman admin melalui URL langsung.
     */
    public function test_teacher_cannot_open_admin_pages(): void
    {
        foreach (['admin.dashboard', 'admin.students.index', 'admin.learning.materials.index', 'admin.attendances.students.create', 'admin.settings.edit'] as $routeName) {
            $this->actingAs($this->teacher)->get(route($routeName))->assertForbidden();
        }
    }

    /**
     * Test tombol keluar pada portal mengakhiri sesi login.
     */
    public function test_logout_from_portal(): void
    {
        $this->actingAs($this->teacher)->post(route('logout'))->assertRedirect();

        $this->assertGuest();
    }
}
