<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Panel Admin') — {{ ($institution ?? \App\Models\Setting::institution())['name'] }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @php
    $manifestPath = public_path('build/manifest.json');
    $isAdminAssetBuilt = file_exists(public_path('hot'))
        || (file_exists($manifestPath) && str_contains((string) file_get_contents($manifestPath), 'resources/css/admin.css'));
    @endphp

    @if ($isAdminAssetBuilt)
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @else
        {{-- Fallback saat aset Vite belum di-build: muat CSS & JS langsung dari resources. --}}
        <style>{!! file_get_contents(resource_path('css/admin.css')) !!}</style>
    @endif

    @stack('styles')
</head>
<body class="admin-body">

@php
$user = auth()->user();
$initials = collect(explode(' ', trim($user->name)))
    ->filter()
    ->take(2)
    ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
    ->implode('');

$institution ??= \App\Models\Setting::institution();
$navCounts ??= [];

/** @var array<int, array{label: string, icon: string, route: string|null, active?: array<int, string>|string, count?: int}> $menuItems */
$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'admin.dashboard'],
    ['label' => 'Manajemen Murid', 'icon' => 'students', 'route' => 'admin.students.index', 'active' => ['admin.students.*', 'admin.enrollments.*']],
    ['label' => 'Data Guru', 'icon' => 'teachers', 'route' => 'admin.teachers.index', 'active' => 'admin.teachers.*'],
    ['label' => 'Program Bimbel', 'icon' => 'programs', 'route' => 'admin.programs.index', 'active' => 'admin.programs.*'],
    ['label' => 'Data Kelas', 'icon' => 'classes', 'route' => 'admin.classes.index', 'active' => 'admin.classes.*'],
    ['label' => 'Jadwal Belajar', 'icon' => 'schedule', 'route' => 'admin.schedules.index', 'active' => 'admin.schedules.*'],
    ['label' => 'Pembayaran', 'icon' => 'payments', 'route' => 'admin.payments.index', 'active' => 'admin.payments.*'],
    ['label' => 'Absensi', 'icon' => 'attendance', 'route' => 'admin.attendances.students.index', 'active' => 'admin.attendances.*'],
    ['label' => 'Pembelajaran', 'icon' => 'learning', 'route' => 'admin.learning.materials.index', 'active' => 'admin.learning.*'],
    ['label' => 'Notifikasi dan Pesan', 'icon' => 'bell', 'route' => 'notifications.index', 'active' => ['notifications.*', 'messages.*', 'admin.announcements.*'], 'count' => ($navCounts['notifications'] ?? 0) + ($navCounts['messages'] ?? 0)],
    ['label' => 'Laporan', 'icon' => 'reports', 'route' => 'admin.reports.students', 'active' => 'admin.reports.*'],
    ['label' => 'Pengaturan', 'icon' => 'settings', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*'],
];
@endphp

<a href="#admin-content" class="admin-skip-link">Lewati ke konten utama</a>

{{-- SIDEBAR --}}
<aside id="admin-sidebar" class="admin-sidebar" aria-label="Navigasi panel admin">
    <div class="admin-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand" aria-label="{{ $institution['name'] }} — Dashboard Admin">
            <img src="{{ $institution['logo_url'] }}" alt="" style="height: 2.25rem; width: auto; max-width: 3rem; object-fit: contain; border-radius: .5rem; background: #fff; padding: .125rem;">
            <span>
                <span class="admin-brand-name">{{ $institution['name'] }}</span>
                <span class="admin-brand-tagline">Panel Admin</span>
            </span>
        </a>
        <button type="button" id="admin-sidebar-close" class="admin-sidebar-close" aria-label="Tutup menu navigasi">
            <x-admin.icon name="close" />
        </button>
    </div>

    <nav class="admin-nav" aria-label="Menu admin">
        <p class="admin-nav-label">Menu Utama</p>
        <ul class="admin-nav-list">
            @foreach ($menuItems as $item)
                <li>
                    @php $isActive = request()->routeIs(...(array) ($item['active'] ?? $item['route'])); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="admin-nav-item {{ $isActive ? 'is-active' : '' }}"
                       @if ($isActive) aria-current="page" @endif>
                        <x-admin.icon :name="$item['icon']" />
                        <span>{{ $item['label'] }}</span>
                        @if (($item['count'] ?? 0) > 0)
                            <span class="admin-count-badge" style="margin-left: auto;" aria-label="{{ $item['count'] }} belum dibaca">{{ $item['count'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="admin-sidebar-footer">
        &copy; {{ date('Y') }} {{ $institution['name'] }}
    </div>
</aside>

<div id="admin-overlay" class="admin-overlay" aria-hidden="true"></div>

{{-- AREA UTAMA --}}
<div class="admin-main">
    <header class="admin-topbar">
        <button type="button"
                id="admin-menu-toggle"
                class="admin-menu-toggle"
                aria-label="Buka menu navigasi"
                aria-controls="admin-sidebar"
                aria-expanded="false">
            <x-admin.icon name="menu" />
        </button>

        <div class="admin-topbar-title">
            <p class="admin-topbar-app">{{ $institution['name'] }}</p>
            <h1 class="admin-topbar-heading">@yield('page_title', 'Dashboard Admin')</h1>
        </div>

        <div style="margin-left: auto; display: flex; align-items: center; gap: .25rem;">
            <a href="{{ route('notifications.index') }}" class="admin-topbar-icon-link" id="topbar-notifications" aria-label="Notifikasi{{ ($navCounts['notifications'] ?? 0) > 0 ? ', '.$navCounts['notifications'].' belum dibaca' : '' }}">
                <x-admin.icon name="bell" />
                @if (($navCounts['notifications'] ?? 0) > 0)<span class="admin-count-badge" aria-hidden="true">{{ $navCounts['notifications'] }}</span>@endif
            </a>
            <a href="{{ route('messages.index') }}" class="admin-topbar-icon-link" id="topbar-messages" aria-label="Pesan{{ ($navCounts['messages'] ?? 0) > 0 ? ', '.$navCounts['messages'].' percakapan belum dibaca' : '' }}">
                <x-admin.icon name="message" />
                @if (($navCounts['messages'] ?? 0) > 0)<span class="admin-count-badge" aria-hidden="true">{{ $navCounts['messages'] }}</span>@endif
            </a>
        </div>

        <div class="admin-profile" style="margin-left: .5rem;">
            <button type="button"
                    id="admin-profile-toggle"
                    class="admin-profile-toggle"
                    aria-label="Menu profil {{ $user->name }}"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-controls="admin-profile-dropdown">
                <span class="admin-avatar" aria-hidden="true">{{ $initials }}</span>
                <span class="admin-profile-meta">
                    <span class="admin-profile-name">{{ $user->name }}</span>
                    <span class="admin-profile-role">{{ $user->role->label() }}</span>
                </span>
                <x-admin.icon name="chevron-down" :size="16" />
            </button>

            <div id="admin-profile-dropdown" class="admin-dropdown" hidden>
                <div class="admin-dropdown-header">
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $user->email }}</span>
                </div>
                <a href="{{ route('home') }}" class="admin-dropdown-item">
                    <x-admin.icon name="globe" :size="16" />
                    Lihat Website
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-dropdown-item is-danger" id="btn-admin-logout">
                        <x-admin.icon name="logout" :size="16" />
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main id="admin-content" class="admin-content" tabindex="-1">
        @yield('content')
    </main>
</div>

{{-- DIALOG KONFIRMASI (dipakai oleh form ber-atribut data-confirm) --}}
<dialog id="admin-confirm-dialog" class="admin-dialog" aria-labelledby="admin-confirm-title" aria-describedby="admin-confirm-message">
    <div class="admin-dialog-body">
        <div class="admin-dialog-icon">
            <x-admin.icon name="trash" :size="20" />
        </div>
        <h2 id="admin-confirm-title">Konfirmasi Penghapusan</h2>
        <p id="admin-confirm-message">Apakah Anda yakin?</p>
    </div>
    <div class="admin-dialog-actions">
        <button type="button" id="admin-confirm-cancel" class="admin-btn admin-btn-secondary">Batal</button>
        <button type="button" id="admin-confirm-submit" class="admin-btn admin-btn-danger">Ya, Hapus</button>
    </div>
</dialog>

@unless ($isAdminAssetBuilt)
    <script>{!! file_get_contents(resource_path('js/admin.js')) !!}</script>
@endunless

@stack('scripts')
</body>
</html>
