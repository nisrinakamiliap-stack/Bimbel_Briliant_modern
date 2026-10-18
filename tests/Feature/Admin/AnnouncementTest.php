<?php

namespace Tests\Feature\Admin;

use App\EnrollmentStatus;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\User;
use App\NotificationCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Jumlah pengumuman yang diterima pengguna.
     */
    private function announcementsOf(User $user): int
    {
        return $user->notifications()->where('data->category', NotificationCategory::Announcement->value)->count();
    }

    /**
     * Test pengumuman untuk semua murid hanya diterima murid.
     */
    public function test_announcement_to_students_reaches_only_students(): void
    {
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.announcements.store'), ['title' => 'Libur Nasional', 'message' => 'Bimbel libur tanggal 17.', 'audience' => 'murid'])
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, $this->announcementsOf($student));
        $this->assertSame(0, $this->announcementsOf($teacher));
        $this->assertSame(0, $this->announcementsOf($this->admin));
    }

    /**
     * Test pengumuman kelas diterima murid yang diterima di kelas tersebut dan guru pengampunya.
     */
    public function test_class_announcement_reaches_approved_students_and_teacher(): void
    {
        $classroom = Classroom::factory()->create();
        $approved = Enrollment::factory()->inClassroom($classroom)->withStatus(EnrollmentStatus::Approved)->create();
        $pending = Enrollment::factory()->inClassroom($classroom)->create();
        $outsider = User::factory()->student()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.announcements.store'), ['title' => 'Ganti Ruang', 'message' => 'Kelas pindah ke ruang 2.', 'audience' => 'kelas', 'classroom_id' => $classroom->id])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $this->announcementsOf($approved->student));
        $this->assertSame(1, $this->announcementsOf($classroom->teacher));
        $this->assertSame(0, $this->announcementsOf($pending->student));
        $this->assertSame(0, $this->announcementsOf($outsider));
    }

    /**
     * Test validasi pengumuman.
     */
    public function test_announcement_is_validated(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.announcements.store'), ['title' => '', 'message' => '', 'audience' => 'kelas'])
            ->assertSessionHasErrors(['title', 'message', 'classroom_id']);

        $this->actingAs($this->admin)
            ->post(route('admin.announcements.store'), ['title' => 'A', 'message' => 'B', 'audience' => 'semua-orang'])
            ->assertSessionHasErrors('audience');
    }

    /**
     * Test guru dan murid tidak dapat membuat pengumuman walaupun membuka URL langsung.
     */
    public function test_non_admin_cannot_send_announcements(): void
    {
        foreach ([User::factory()->teacher()->create(), User::factory()->student()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.announcements.create'))->assertForbidden();
            $this->actingAs($user)
                ->post(route('admin.announcements.store'), ['title' => 'Palsu', 'message' => 'Palsu', 'audience' => 'semua'])
                ->assertForbidden();
        }

        $this->assertSame(0, $this->announcementsOf($this->admin));
    }

    /**
     * Test form pengumuman dapat dibuka admin.
     */
    public function test_admin_can_open_announcement_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.announcements.create'))
            ->assertOk()
            ->assertSee('Semua murid');
    }
}
