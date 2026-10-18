<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pesan langsung antarpengguna (semua role).
 *
 * Percakapan hanya dapat dibuka oleh pesertanya, termasuk admin; admin tidak dapat membuka
 * percakapan pengguna lain. Lawan bicara dibatasi oleh {@see User::canMessage()}.
 */
class MessageController extends Controller
{
    /**
     * Daftar percakapan milik pengguna, terbaru di atas.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->forUser($user)
            ->whereNotNull('last_message_at')
            ->with(['participants', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('messages.index', [
            'layout' => $this->layoutFor($user),
            'conversations' => $conversations,
            'user' => $user,
        ]);
    }

    /**
     * Form pesan baru dengan pilihan lawan bicara yang diizinkan.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        return view('messages.create', [
            'layout' => $this->layoutFor($user),
            'contacts' => $user->messageableContacts(),
            'preselectedRecipientId' => $request->integer('kepada') ?: null,
        ]);
    }

    /**
     * Memulai (atau melanjutkan) percakapan dengan pengguna lain dan mengirim pesan pertama.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:2000'],
        ], $this->messages());

        $sender = $request->user();
        $recipient = User::query()->find($validated['recipient_id']);

        if ($recipient === null || ! $sender->canMessage($recipient)) {
            throw ValidationException::withMessages(['recipient_id' => 'Anda tidak dapat mengirim pesan kepada pengguna ini.']);
        }

        $conversation = DB::transaction(function () use ($sender, $recipient, $validated): Conversation {
            $conversation = Conversation::between($sender, $recipient);
            $this->appendMessage($conversation, $sender, $validated['body']);

            return $conversation;
        });

        return redirect()->route('messages.show', $conversation)->with('success', 'Pesan terkirim.');
    }

    /**
     * Membuka percakapan dan menandai pesan sudah dibaca.
     */
    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();
        abort_unless($conversation->hasParticipant($user), 403, 'Anda bukan peserta percakapan ini.');

        $conversation->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
            'last_read_message_id' => $conversation->messages()->max('id'),
        ]);
        $conversation->load(['participants', 'messages' => fn ($query) => $query->with('sender')->oldest()]);

        return view('messages.show', [
            'layout' => $this->layoutFor($user),
            'conversation' => $conversation,
            'other' => $conversation->otherParticipant($user),
            'user' => $user,
            'canReply' => ($other = $conversation->otherParticipant($user)) !== null && $user->canMessage($other),
        ]);
    }

    /**
     * Membalas pesan dalam percakapan.
     */
    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();
        abort_unless($conversation->hasParticipant($user), 403, 'Anda bukan peserta percakapan ini.');

        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']], $this->messages());

        $other = $conversation->load('participants')->otherParticipant($user);
        if ($other === null || ! $user->canMessage($other)) {
            throw ValidationException::withMessages(['body' => 'Percakapan ini tidak dapat dibalas lagi.']);
        }

        DB::transaction(fn () => $this->appendMessage($conversation, $user, $validated['body']));

        return redirect()->route('messages.show', $conversation);
    }

    /**
     * Menambahkan pesan dan memperbarui waktu pesan terakhir serta status baca pengirim.
     */
    private function appendMessage(Conversation $conversation, User $sender, string $body): Message
    {
        $message = new Message(['body' => trim($body)]);
        $message->conversation_id = $conversation->id;
        $message->sender_id = $sender->id;
        $message->save();

        $conversation->last_message_at = $message->created_at;
        $conversation->save();
        $conversation->participants()->updateExistingPivot($sender->id, [
            'last_read_at' => $message->created_at,
            'last_read_message_id' => $message->id,
        ]);

        return $message;
    }

    /**
     * Pesan validasi.
     *
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'recipient_id.required' => 'Silakan pilih penerima pesan.',
            'body.required' => 'Isi pesan wajib diisi.',
            'body.max' => 'Pesan maksimal 2000 karakter.',
        ];
    }

    /**
     * Layout sesuai role: panel admin untuk admin, portal untuk guru/murid.
     */
    private function layoutFor(User $user): string
    {
        return $user->hasRole(UserRole::Admin) ? 'layouts.admin' : 'layouts.portal';
    }
}
