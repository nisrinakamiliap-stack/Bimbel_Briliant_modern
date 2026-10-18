<?php

namespace Tests\Feature;

use App\EnrollmentStatus;
use App\Models\Classroom;
use App\Models\Conversation;
use App\Models\Enrollment;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['name' => 'Admin Utama']);
        $this->teacher = User::factory()->teacher()->create(['name' => 'Pak Budi']);
        $this->student = User::factory()->student()->create(['name' => 'Dewi Lestari']);

        $classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id]);
        Enrollment::factory()->inClassroom($classroom)->withStatus(EnrollmentStatus::Approved)->create(['student_id' => $this->student->id]);
    }

    /**
     * Mengirim pesan pertama dan mengembalikan percakapannya.
     */
    private function sendMessage(User $sender, User $recipient, string $body): Conversation
    {
        $this->actingAs($sender)
            ->post(route('messages.store'), ['recipient_id' => $recipient->id, 'body' => $body])
            ->assertSessionHasNoErrors();

        return Conversation::query()->where('participant_key', Conversation::participantKey($sender, $recipient))->firstOrFail();
    }

    /**
     * Test murid dapat mengirim pesan ke guru kelasnya dan guru melihatnya sebagai belum dibaca.
     */
    public function test_student_can_message_own_teacher(): void
    {
        $conversation = $this->sendMessage($this->student, $this->teacher, 'Pak, besok ada tugas?');

        $this->assertDatabaseHas('messages', ['conversation_id' => $conversation->id, 'sender_id' => $this->student->id, 'body' => 'Pak, besok ada tugas?']);
        $this->assertSame(1, $this->teacher->unreadConversationsCount());
        $this->assertSame(0, $this->student->unreadConversationsCount());

        $this->actingAs($this->teacher)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertSee('Pak, besok ada tugas?');
    }

    /**
     * Test membuka percakapan menandai pesan sudah dibaca, lalu balasan terkirim.
     */
    public function test_opening_conversation_marks_it_read_and_reply_works(): void
    {
        $conversation = $this->sendMessage($this->student, $this->teacher, 'Halo Pak');

        $this->actingAs($this->teacher)
            ->get(route('messages.show', $conversation))
            ->assertOk()
            ->assertSee('Halo Pak');
        $this->assertSame(0, $this->teacher->unreadConversationsCount());

        $this->actingAs($this->teacher)
            ->post(route('messages.reply', $conversation), ['body' => 'Halo Dewi'])
            ->assertRedirect(route('messages.show', $conversation));

        $this->assertSame(2, $conversation->messages()->count());
        $this->assertSame(1, $this->student->unreadConversationsCount());
    }

    /**
     * Test balasan pada detik yang sama dengan waktu baca tetap terhitung belum dibaca.
     */
    public function test_reply_in_the_same_second_is_still_unread(): void
    {
        $this->freezeSecond();

        $conversation = $this->sendMessage($this->student, $this->teacher, 'Halo Pak');
        $this->actingAs($this->teacher)->get(route('messages.show', $conversation))->assertOk();
        $this->actingAs($this->teacher)->post(route('messages.reply', $conversation), ['body' => 'Halo Dewi']);

        $this->assertSame(1, $this->student->unreadConversationsCount());
        $this->assertSame(0, $this->teacher->unreadConversationsCount());
        $this->assertSame(1, $conversation->fresh('participants')->unreadCountFor($this->student));

        $this->actingAs($this->student)->get(route('messages.show', $conversation))->assertOk();
        $this->assertSame(0, $this->student->unreadConversationsCount());
    }

    /**
     * Test pesan berikutnya ke orang yang sama memakai percakapan yang sama.
     */
    public function test_repeated_messages_reuse_the_same_conversation(): void
    {
        $this->sendMessage($this->admin, $this->student, 'Pesan pertama');
        $this->sendMessage($this->admin, $this->student, 'Pesan kedua');
        $this->sendMessage($this->student, $this->admin, 'Balasan murid');

        $this->assertSame(1, Conversation::query()->count());
        $this->assertSame(3, Message::query()->count());
    }

    /**
     * Test admin tidak dapat membuka atau membalas percakapan privat guru dan murid.
     */
    public function test_admin_cannot_access_private_conversation_of_others(): void
    {
        $conversation = $this->sendMessage($this->student, $this->teacher, 'Pesan privat');

        $this->actingAs($this->admin)->get(route('messages.show', $conversation))->assertForbidden();
        $this->actingAs($this->admin)->post(route('messages.reply', $conversation), ['body' => 'Menyusup'])->assertForbidden();
        $this->actingAs($this->admin)->get(route('messages.index'))->assertOk()->assertDontSee('Pesan privat');

        $this->assertSame(1, $conversation->messages()->count());
    }

    /**
     * Test murid lain tidak dapat membuka percakapan yang bukan miliknya.
     */
    public function test_other_student_cannot_access_conversation(): void
    {
        $conversation = $this->sendMessage($this->student, $this->teacher, 'Pesan privat');
        $otherStudent = User::factory()->student()->create();

        $this->actingAs($otherStudent)->get(route('messages.show', $conversation))->assertForbidden();
    }

    /**
     * Test murid tidak dapat mengirim pesan ke guru yang tidak mengajarnya atau ke murid lain.
     */
    public function test_student_cannot_message_unrelated_users(): void
    {
        $otherTeacher = User::factory()->teacher()->create();
        $otherStudent = User::factory()->student()->create();

        foreach ([$otherTeacher, $otherStudent, $this->student] as $recipient) {
            $this->actingAs($this->student)
                ->post(route('messages.store'), ['recipient_id' => $recipient->id, 'body' => 'Halo'])
                ->assertSessionHasErrors('recipient_id');
        }

        $this->assertSame(0, Message::query()->count());
    }

    /**
     * Test pilihan penerima pada form hanya berisi kontak yang diizinkan.
     */
    public function test_create_form_lists_only_allowed_contacts(): void
    {
        $otherTeacher = User::factory()->teacher()->create(['name' => 'Bu Guru Lain']);

        $this->actingAs($this->student)
            ->get(route('messages.create'))
            ->assertOk()
            ->assertSee('Pak Budi')
            ->assertSee('Admin Utama')
            ->assertDontSee('Bu Guru Lain');
    }

    /**
     * Test pesan kosong atau terlalu panjang ditolak.
     */
    public function test_message_body_is_validated(): void
    {
        $this->actingAs($this->student)
            ->post(route('messages.store'), ['recipient_id' => $this->teacher->id, 'body' => ''])
            ->assertSessionHasErrors('body');

        $this->actingAs($this->student)
            ->post(route('messages.store'), ['recipient_id' => $this->teacher->id, 'body' => str_repeat('a', 2001)])
            ->assertSessionHasErrors('body');
    }

    /**
     * Test isi pesan ditampilkan ter-escape sehingga tidak menjalankan skrip.
     */
    public function test_message_body_is_escaped(): void
    {
        $conversation = $this->sendMessage($this->student, $this->admin, '<script>alert(1)</script>');

        $this->actingAs($this->admin)
            ->get(route('messages.show', $conversation))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }
}
