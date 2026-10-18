@extends($layout)

@section('title', 'Pesan dengan '.($other?->name ?? 'Pengguna'))
@section('page_title', 'Notifikasi dan Pesan')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('messages.index') }}">Pesan</a></li>
                <li aria-current="page">{{ $other?->name ?? 'Pengguna' }}</li>
            </ol>
            <h2>{{ $other?->name ?? 'Pengguna' }}</h2>
            <p>{{ $other?->role->label() }}{{ $other?->trashed() ? ' · akun dihapus' : '' }}</p>
        </div>
        <a href="{{ route('messages.index') }}" class="admin-btn admin-btn-secondary"><x-admin.icon name="arrow-left" :size="16" /> Semua Pesan</a>
    </div>

    <x-admin.flash />

    <section class="admin-panel" aria-labelledby="chat-heading">
        <h3 id="chat-heading" class="sr-only">Isi percakapan</h3>
        <div class="admin-chat" id="chat-messages" aria-live="polite">
            @foreach ($conversation->messages as $message)
                <div class="admin-chat-bubble {{ $message->sender_id === $user->id ? 'is-mine' : '' }}">
                    {{ $message->body }}
                    <span class="admin-chat-meta">{{ $message->sender_id === $user->id ? 'Anda' : $message->sender->name }} · {{ $message->created_at->translatedFormat('d M Y, H:i') }}</span>
                </div>
            @endforeach
        </div>

        @if ($canReply)
            <form action="{{ route('messages.reply', $conversation) }}" method="POST" class="admin-form" style="border-top: 1px solid var(--admin-border);">
                @csrf
                <label for="body" class="sr-only">Balas pesan</label>
                <textarea id="body" name="body" class="admin-textarea {{ $errors->has('body') ? 'is-invalid' : '' }}" rows="3" maxlength="2000" placeholder="Tulis balasan..." required>{{ old('body') }}</textarea>
                @error('body') <p class="admin-error">{{ $message }}</p> @enderror
                <div class="admin-form-actions" style="margin-top: .75rem; padding-top: 0; border-top: 0;">
                    <button type="submit" class="admin-btn admin-btn-primary" id="btn-reply">Kirim</button>
                </div>
            </form>
        @else
            <p class="admin-result-info">Percakapan ini tidak dapat dibalas karena hak berkirim pesan sudah tidak berlaku.</p>
        @endif
    </section>

    @push('scripts')
    <script>(function () { var chat = document.getElementById('chat-messages'); if (chat) chat.scrollTop = chat.scrollHeight; }());</script>
    @endpush
@endsection
