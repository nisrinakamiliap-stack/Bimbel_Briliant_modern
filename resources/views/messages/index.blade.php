@extends($layout)

@section('title', 'Pesan')
@section('page_title', 'Notifikasi dan Pesan')

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Pesan</h2>
            <p>Percakapan langsung Anda. Hanya peserta percakapan yang dapat membaca isinya.</p>
        </div>
        <a href="{{ route('messages.create') }}" class="admin-btn admin-btn-primary" id="btn-new-message"><x-admin.icon name="plus" :size="16" /> Pesan Baru</a>
    </div>

    @include('notifications.partials.tabs')

    <section class="admin-panel" aria-labelledby="conversations-heading">
        <h3 id="conversations-heading" class="sr-only">Daftar percakapan</h3>
        @if ($conversations->isEmpty())
            <x-admin.empty-state title="Belum ada percakapan" icon="inbox">Mulai percakapan melalui tombol Pesan Baru.</x-admin.empty-state>
        @else
            <ul class="admin-list" id="conversation-list">
                @foreach ($conversations as $conversation)
                    @php
                        $other = $conversation->otherParticipant($user);
                        $unread = $conversation->unreadCountFor($user);
                    @endphp
                    <li class="admin-list-item {{ $unread > 0 ? 'is-unread' : '' }}">
                        <span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($other?->name ?? '?', 0, 1)) }}</span>
                        <div class="admin-list-item-body">
                            <p class="admin-list-item-title">
                                <a href="{{ route('messages.show', $conversation) }}" style="color: inherit; text-decoration: none;">{{ $other?->name ?? 'Pengguna' }}</a>
                                <span class="admin-cell-sub" style="display: inline;">· {{ $other?->role->label() }}</span>
                                @if ($unread > 0)<span class="admin-count-badge">{{ $unread }} baru</span>@endif
                            </p>
                            <p style="margin: 0; font-size: .875rem; color: var(--admin-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $conversation->latestMessage?->sender_id === $user->id ? 'Anda: ' : '' }}{{ \Illuminate\Support\Str::limit($conversation->latestMessage?->body, 90) }}
                            </p>
                            <p class="admin-list-item-meta">{{ $conversation->last_message_at?->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
            @if ($conversations->hasPages())
                {{ $conversations->links('admin.partials.pagination') }}
            @endif
        @endif
    </section>
@endsection
