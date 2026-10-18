{{-- Tombol Pesan mengambang. Diklik untuk membuka halaman Pesan; disembunyikan saat sudah berada di halaman Pesan. --}}
@php $unreadConversations = $navCounts['messages'] ?? 0; @endphp
@unless (request()->routeIs('messages.*'))
    <a href="{{ route('messages.index') }}" id="teacher-message-button" class="teacher-message-button"
       aria-label="Pesan{{ $unreadConversations > 0 ? ", {$unreadConversations} percakapan belum dibaca" : '' }}">
        <x-admin.icon name="message" :size="22" />
        <span class="teacher-message-button-label">Pesan</span>
        @if ($unreadConversations > 0)
            <span class="admin-count-badge" aria-hidden="true">{{ $unreadConversations > 99 ? '99+' : $unreadConversations }}</span>
        @endif
    </a>
@endunless
