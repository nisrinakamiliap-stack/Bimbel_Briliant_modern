<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Percakapan langsung antara dua pengguna.
 *
 * Seluruh kolom diatur oleh sistem; tidak ada atribut yang dapat diisi melalui mass assignment.
 */
class Conversation extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * Peserta percakapan beserta waktu terakhir membaca.
     *
     * @return BelongsToMany<User, $this>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['last_read_at', 'last_read_message_id'])
            ->withTimestamps()
            ->withTrashed();
    }

    /**
     * Pesan dalam percakapan.
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Pesan terakhir.
     *
     * @return HasOne<Message, $this>
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Membatasi query pada percakapan yang diikuti pengguna tertentu.
     *
     * @param  Builder<Conversation>  $query
     * @return Builder<Conversation>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->whereHas('participants', fn (Builder $query) => $query->whereKey($user->id));
    }

    /**
     * Menentukan apakah pengguna adalah peserta percakapan.
     */
    public function hasParticipant(User $user): bool
    {
        return $this->participants()->whereKey($user->id)->exists();
    }

    /**
     * Lawan bicara pengguna dalam percakapan ini.
     */
    public function otherParticipant(User $user): ?User
    {
        return $this->participants->first(fn (User $participant) => ! $participant->is($user));
    }

    /**
     * Jumlah pesan yang belum dibaca pengguna (pesan dari lawan bicara setelah terakhir membaca).
     */
    public function unreadCountFor(User $user): int
    {
        $participant = $this->participants->firstWhere('id', $user->id);
        $lastReadMessageId = (int) ($participant?->pivot?->last_read_message_id ?? 0);

        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('id', '>', $lastReadMessageId)
            ->count();
    }

    /**
     * Kunci unik pasangan peserta, misalnya "3:8".
     */
    public static function participantKey(User $first, User $second): string
    {
        $ids = [$first->id, $second->id];
        sort($ids);

        return implode(':', $ids);
    }

    /**
     * Percakapan antara dua pengguna; dibuat jika belum ada. Wajib dipanggil di dalam transaksi.
     */
    public static function between(User $first, User $second): self
    {
        $conversation = static::query()->where('participant_key', static::participantKey($first, $second))->lockForUpdate()->first();

        if ($conversation !== null) {
            return $conversation;
        }

        $conversation = new self;
        $conversation->participant_key = static::participantKey($first, $second);
        $conversation->save();
        $conversation->participants()->attach([$first->id, $second->id]);

        return $conversation;
    }
}
