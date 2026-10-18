<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Portal') — {{ $institution['name'] ?? 'Bimbel Sentra Edukasi' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @php
    $manifestPath = public_path('build/manifest.json');
    $isAdminAssetBuilt = file_exists(public_path('hot'))
        || (file_exists($manifestPath) && str_contains((string) file_get_contents($manifestPath), 'resources/css/admin.css'));
    $portalUser = auth()->user();
    $portalLinks = array_values(array_filter([
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => '*.dashboard'],
        $portalUser->hasRole(\App\UserRole::Student) ? ['label' => 'Pembelajaran', 'route' => 'student.learning.index', 'active' => 'student.learning.*'] : null,
        ['label' => 'Pesan', 'route' => 'messages.index', 'active' => 'messages.*', 'count' => $navCounts['messages'] ?? 0],
        ['label' => 'Notifikasi', 'route' => 'notifications.index', 'active' => 'notifications.*', 'count' => $navCounts['notifications'] ?? 0],
    ]));
    @endphp

    @if ($isAdminAssetBuilt)
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/admin.css')) !!}</style>
    @endif
</head>
<body class="admin-body">
<a href="#portal-content" class="admin-skip-link">Lewati ke konten utama</a>

<header class="admin-topbar" style="flex-wrap: wrap; height: auto; min-height: var(--admin-topbar-height); padding-top: .5rem; padding-bottom: .5rem;">
    <a href="{{ route('home') }}" class="admin-brand" style="color: var(--admin-forest);">
        <span class="admin-brand-logo" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7L12 12L22 7L12 2Z" fill="white"/><path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <span style="font-weight: 800;">{{ $institution['name'] ?? 'Bimbel Sentra Edukasi' }}</span>
    </a>
    <nav class="admin-view-switch" aria-label="Menu portal" style="margin-left: auto; flex-wrap: wrap;">
        @foreach ($portalLinks as $link)
            @php $isActiveLink = request()->routeIs($link['active']); @endphp
            <a href="{{ route($link['route']) }}" class="{{ $isActiveLink ? 'is-active' : '' }}" @if ($isActiveLink) aria-current="page" @endif>
                {{ $link['label'] }}
                @if (($link['count'] ?? 0) > 0)
                    <span class="admin-count-badge" aria-label="{{ $link['count'] }} belum dibaca">{{ $link['count'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>
    <span style="font-size: .875rem; color: var(--admin-muted);">{{ $portalUser->name }} · {{ $portalUser->role->label() }}</span>
    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
        @csrf
        <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm">Keluar</button>
    </form>
</header>

<main id="portal-content" class="admin-content" tabindex="-1">
    @yield('content')
</main>

<dialog id="admin-confirm-dialog" class="admin-dialog" aria-labelledby="admin-confirm-title" aria-describedby="admin-confirm-message">
    <div class="admin-dialog-body">
        <h2 id="admin-confirm-title">Konfirmasi</h2>
        <p id="admin-confirm-message">Apakah Anda yakin?</p>
    </div>
    <div class="admin-dialog-actions">
        <button type="button" id="admin-confirm-cancel" class="admin-btn admin-btn-secondary">Batal</button>
        <button type="button" id="admin-confirm-submit" class="admin-btn admin-btn-danger">Ya, Lanjutkan</button>
    </div>
</dialog>

@unless ($isAdminAssetBuilt)
    <script>{!! file_get_contents(resource_path('js/admin.js')) !!}</script>
@endunless
@stack('scripts')
</body>
</html>
