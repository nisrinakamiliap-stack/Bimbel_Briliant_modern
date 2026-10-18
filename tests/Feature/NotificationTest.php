<?php

namespace Tests\Feature;

use App\EnrollmentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\NotificationCategory;
use App\Notifications\Announcement;
use App\Notifications\NotificationSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['name' => 'Admin Utama']);
    }

    /**
     * Membuat notifikasi pengumuman untuk pengguna dengan waktu pembuatan tertentu.
     */
    private function announce(User $user, string $title, ?string $createdAt = null): DatabaseNotification
    {
        $user->notify(new Announcement((string) Str::uuid(), $title, "Isi {$title}"));
        $notification = $user->notifications()->latest()->firstOrFail();

        if ($createdAt !== null) {
            $notification->forceFill(['created_at' => $createdAt])->save();
        }

        return $notification;
    }

    /**
     * Test murid baru yang mendaftar menghasilkan satu notifikasi untuk setiap admin.
     */
    public function test_new_student_registration_notifies_every_admin_once(): void
    {
        $secondAdmin = User::factory()->admin()->create();

        $student = User::factory()->student()->create(['name' => 'Dewi Lestari']);

        foreach ([$this->admin, $secondAdmin] as $admin) {
            $notifications = $admin->notifications()->where('data->dedupe_key', "student-registered:{$student->id}")->get();
            $this->assertCount(1, $notifications);
            $this->assertSame(NotificationCategory::Registration->value, $notifications->first()->data['category']);
        }

        $this->assertSame(0, $student->notifications()->count());
    }

    /**
     * Test pembayaran yang dicatat admin tidak memberi tahu admin pencatat, tetapi admin lain diberi tahu.
     */
    public function test_payment_notification_skips_the_admin_who_recorded_it(): void
    {
        $otherAdmin = User::factory()->admin()->create();
        $enrollment = Enrollment::factory()->withStatus(EnrollmentStatus::Approved)->create();

        $this->actingAs($this->admin);
        $payment = Payment::factory()->for($enrollment)->create();

        $this->assertFalse($this->admin->notifications()->where('data->dedupe_key', "payment-created:{$payment->id}")->exists());
        $this->assertTrue($otherAdmin->notifications()->where('data->dedupe_key', "payment-created:{$payment->id}")->exists());
    }

    /**
     * Test peristiwa yang sama tidak menghasilkan notifikasi ganda.
     */
    public function test_same_event_is_never_notified_twice(): void
    {
        $student = User::factory()->student()->create();
        $notification = new Announcement('pengumuman-1', 'Libur', 'Bimbel libur besok.');

        $this->assertSame(1, NotificationSender::send($student, $notification));
        $this->assertSame(0, NotificationSender::send($student, $notification));
        $this->assertSame(1, $student->notifications()->count());
    }

    /**
     * Test tugas terbit memberi tahu murid peserta kelas sekali saja walaupun tugas disimpan ulang.
     */
    public function test_published_assignment_notifies_approved_students_once(): void
    {
        $classroom = Classroom::factory()->create();
        $approved = Enrollment::factory()->inClassroom($classroom)->withStatus(EnrollmentStatus::Approved)->create();
        $pending = Enrollment::factory()->inClassroom($classroom)->create();

        $assignment = Assignment::factory()->for($classroom)->create();
        $assignment->update(['title' => 'Tugas Revisi']);

        $this->assertSame(1, $approved->student->notifications()->where('data->category', NotificationCategory::Assignment->value)->count());
        $this->assertSame(0, $pending->student->notifications()->where('data->category', NotificationCategory::Assignment->value)->count());
    }

    /**
     * Test tugas draf tidak menghasilkan notifikasi.
     */
    public function test_draft_assignment_does_not_notify_students(): void
    {
        $classroom = Classroom::factory()->create();
        $approved = Enrollment::factory()->inClassroom($classroom)->withStatus(EnrollmentStatus::Approved)->create();

        Assignment::factory()->for($classroom)->draft()->create();

        $this->assertSame(0, $approved->student->notifications()->where('data->category', NotificationCategory::Assignment->value)->count());
    }

    /**
     * Test halaman notifikasi hanya menampilkan notifikasi milik pengguna sendiri.
     */
    public function test_index_shows_only_own_notifications(): void
    {
        $other = User::factory()->admin()->create();
        $this->announce($this->admin, 'Pengumuman Saya');
        $this->announce($other, 'Pengumuman Orang Lain');

        $this->actingAs($this->admin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Pengumuman Saya')
            ->assertDontSee('Pengumuman Orang Lain');
    }

    /**
     * Test filter kategori, status baca, dan tanggal.
     */
    public function test_index_filters_by_category_status_and_date(): void
    {
        $this->admin->notifications()->delete();
        $read = $this->announce($this->admin, 'Pengumuman Lama', '2026-09-01 10:00:00');
        $read->markAsRead();
        $this->announce($this->admin, 'Pengumuman Baru', '2026-10-10 10:00:00');
        User::factory()->student()->create(['name' => 'Murid Pendaftar']);

        $this->actingAs($this->admin)
            ->get(route('notifications.index', ['kategori' => NotificationCategory::Announcement->value]))
            ->assertOk()
            ->assertSee('Pengumuman Lama')
            ->assertSee('Pengumuman Baru')
            ->assertDontSee('Murid Pendaftar');

        $this->actingAs($this->admin)
            ->get(route('notifications.index', ['status' => 'sudah']))
            ->assertOk()
            ->assertSee('Pengumuman Lama')
            ->assertDontSee('Pengumuman Baru');

        $this->actingAs($this->admin)
            ->get(route('notifications.index', ['status' => 'belum', 'kategori' => NotificationCategory::Announcement->value]))
            ->assertOk()
            ->assertSee('Pengumuman Baru')
            ->assertDontSee('Pengumuman Lama');

        $this->actingAs($this->admin)
            ->get(route('notifications.index', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertOk()
            ->assertSee('Pengumuman Baru')
            ->assertDontSee('Pengumuman Lama');
    }

    /**
     * Test filter dengan nilai tidak valid ditolak.
     */
    public function test_invalid_filters_are_rejected(): void
    {
        $this->actingAs($this->admin)
            ->get(route('notifications.index', ['kategori' => 'tidak-ada', 'dari' => '2026-10-10', 'sampai' => '2026-10-01']))
            ->assertSessionHasErrors(['kategori', 'sampai']);
    }

    /**
     * Test membuka notifikasi menandainya sudah dibaca dan mengarahkan ke tautan internal.
     */
    public function test_open_marks_notification_as_read_and_redirects_to_internal_url(): void
    {
        $student = User::factory()->student()->create();
        $notification = $this->admin->notifications()->where('data->dedupe_key', "student-registered:{$student->id}")->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('admin.students.show', $student));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    /**
     * Test tautan luar pada notifikasi tidak diikuti (mencegah open redirect).
     */
    public function test_open_ignores_external_urls(): void
    {
        $notification = $this->announce($this->admin, 'Tautan Luar');
        $notification->forceFill(['data' => [...$notification->data, 'url' => 'https://contoh-berbahaya.test/']])->save();

        $this->actingAs($this->admin)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('notifications.index'));
    }

    /**
     * Test menandai satu dan seluruh notifikasi sudah dibaca.
     */
    public function test_mark_one_and_all_as_read(): void
    {
        $first = $this->announce($this->admin, 'Pertama');
        $this->announce($this->admin, 'Kedua');

        $this->actingAs($this->admin)
            ->from(route('notifications.index'))
            ->patch(route('notifications.read', $first->id))
            ->assertRedirect(route('notifications.index'));
        $this->assertNotNull($first->fresh()->read_at);

        $this->actingAs($this->admin)
            ->from(route('notifications.index'))
            ->post(route('notifications.read-all'))
            ->assertRedirect(route('notifications.index'));
        $this->assertSame(0, $this->admin->unreadNotifications()->count());
    }

    /**
     * Test hanya notifikasi yang sudah dibaca yang dapat dihapus.
     */
    public function test_only_read_notifications_can_be_deleted(): void
    {
        $notification = $this->announce($this->admin, 'Untuk Dihapus');

        $this->actingAs($this->admin)
            ->from(route('notifications.index'))
            ->delete(route('notifications.destroy', $notification->id))
            ->assertSessionHas('error');
        $this->assertNotNull($notification->fresh());

        $notification->markAsRead();

        $this->actingAs($this->admin)
            ->from(route('notifications.index'))
            ->delete(route('notifications.destroy', $notification->id))
            ->assertSessionHas('success');
        $this->assertNull($notification->fresh());
    }

    /**
     * Test pengguna tidak dapat membaca, menandai, atau menghapus notifikasi milik orang lain.
     */
    public function test_users_cannot_touch_notifications_of_others(): void
    {
        $student = User::factory()->student()->create();
        $notification = $this->announce($this->admin, 'Rahasia Admin');
        $notification->markAsRead();

        $this->actingAs($student)->get(route('notifications.open', $notification->id))->assertNotFound();
        $this->actingAs($student)->patch(route('notifications.read', $notification->id))->assertNotFound();
        $this->actingAs($student)->delete(route('notifications.destroy', $notification->id))->assertNotFound();

        $this->assertNotNull($notification->fresh());
    }

    /**
     * Test murid membuka notifikasi melalui layout portal, bukan panel admin.
     */
    public function test_student_sees_notifications_in_portal_layout(): void
    {
        $student = User::factory()->student()->create();
        $this->announce($student, 'Jadwal Libur');

        $this->actingAs($student)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Jadwal Libur')
            ->assertDontSee('Menu Utama');
    }

    /**
     * Test tamu diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }
}
