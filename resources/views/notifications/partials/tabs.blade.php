{{-- Navigasi internal Notifikasi dan Pesan. --}}
@php
$communicationTabs = array_values(array_filter([
    ['label' => 'Notifikasi', 'route' => 'notifications.index', 'active' => 'notifications.*', 'count' => $navCounts['notifications'] ?? 0],
    ['label' => 'Pesan', 'route' => 'messages.index', 'active' => 'messages.*', 'count' => $navCounts['messages'] ?? 0],
    auth()->user()->hasRole(\App\UserRole::Admin) ? ['label' => 'Buat Pengumuman', 'route' => 'admin.announcements.create', 'active' => 'admin.announcements.*'] : null,
]));
@endphp
<nav class="admin-view-switch" aria-label="Notifikasi dan Pesan" style="margin-bottom: 1.25rem; flex-wrap: wrap;">
    @foreach ($communicationTabs as $tab)
        @php $isActiveTab = request()->routeIs($tab['active']); @endphp
        <a href="{{ route($tab['route']) }}" class="{{ $isActiveTab ? 'is-active' : '' }}" @if ($isActiveTab) aria-current="page" @endif>
            {{ $tab['label'] }}
            @if (($tab['count'] ?? 0) > 0)<span class="admin-count-badge">{{ $tab['count'] }}</span>@endif
        </a>
    @endforeach
</nav>
