{{-- Navbar Portal Murid. Menu aktif mengikuti route. --}}
@php
    $student = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($student->name)))->filter()->take(2)
        ->map(fn (string $p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');

    $studentProfile = $student->studentProfile;
    $classroomIds   = $student->approvedClassroomIds();

    // Nama kelas aktif pertama untuk label profil
    $firstClass = null;
    if (!empty($classroomIds)) {
        $firstClass = \App\Models\Classroom::query()
            ->whereIn('id', $classroomIds)
            ->with('program')
            ->first();
    }

    $unreadNotifications = $navCounts['notifications'] ?? 0;

    $navItems = [
        ['label' => 'Beranda',        'route' => 'student.dashboard',       'active' => 'student.dashboard'],
        ['label' => 'Jadwal Belajar', 'route' => 'student.schedules.index', 'active' => 'student.schedules.*'],
        ['label' => 'Kelas Saya',     'route' => 'student.classes.index',   'active' => ['student.classes.*', 'student.materials.*', 'student.learning.*']],
        ['label' => 'Pembayaran',     'route' => 'student.payments.index',  'active' => 'student.payments.*'],
    ];
@endphp

<header class="st-nav" role="banner">
    <div class="st-container st-nav-inner">

        {{-- Brand --}}
        <a href="{{ route('student.dashboard') }}" class="st-brand" aria-label="{{ $institution['name'] }}, Portal Murid: Beranda">
            <img src="{{ $institution['logo_url'] }}" alt="" width="40" height="40">
            <span class="st-brand-text">
                <span class="st-brand-name">{{ $institution['name'] }}</span>
                <span class="st-brand-label">Portal Murid</span>
            </span>
        </a>

        {{-- Menu desktop --}}
        <nav aria-label="Menu Portal Murid">
            <ul class="st-nav-links" role="menubar">
                @foreach ($navItems as $item)
                    @php
                        $activePatterns = (array) ($item['active'] ?? []);
                        $isActive = request()->routeIs(...$activePatterns);
                    @endphp
                    <li @class(['st-dropdown-wrap' => isset($item['children'])]) role="none">
                        @isset($item['children'])
                            <button type="button"
                                    id="st-learning-toggle"
                                    class="st-nav-link {{ $isActive ? 'is-active' : '' }}"
                                    aria-expanded="false"
                                    aria-controls="st-learning-menu"
                                    @if($isActive) aria-current="page" @endif
                                    role="menuitem">
                                {{ $item['label'] }}
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <ul id="st-learning-menu" class="st-dropdown" hidden role="menu" aria-label="Menu Pembelajaran">
                                @foreach ($item['children'] as $child)
                                    @php $isChildActive = request()->routeIs($child['active']); @endphp
                                    <li role="none">
                                        <a href="{{ route($child['route']) }}"
                                           class="st-dropdown-item {{ $isChildActive ? 'is-active' : '' }}"
                                           @if($isChildActive) aria-current="page" @endif
                                           role="menuitem">
                                            {{ $child['label'] }}
                                            <small>{{ $child['hint'] }}</small>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <a href="{{ route($item['route']) }}"
                               class="st-nav-link {{ $isActive ? 'is-active' : '' }}"
                               @if($isActive) aria-current="page" @endif
                               role="menuitem">
                                {{ $item['label'] }}
                                @if (($item['count'] ?? 0) > 0)
                                    <span class="admin-count-badge" aria-label="{{ $item['count'] }} pesan belum dibaca">{{ $item['count'] }}</span>
                                @endif
                            </a>
                        @endisset
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Kanan: notifikasi + profil --}}
        <div class="st-nav-actions">
            {{-- Notifikasi --}}
            <a href="{{ route('notifications.index') }}"
               class="st-icon-btn {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}"
               aria-label="Notifikasi{{ $unreadNotifications > 0 ? ", {$unreadNotifications} belum dibaca" : '' }}"
               @if(request()->routeIs('notifications.*')) aria-current="page" @endif>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                @if ($unreadNotifications > 0)
                    <span class="st-badge" aria-hidden="true">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                @endif
            </a>

            {{-- Profil --}}
            <div class="st-dropdown-wrap">
                <button type="button"
                        id="st-profile-toggle"
                        class="st-profile-btn {{ request()->routeIs('student.profile.*') ? 'is-active' : '' }}"
                        aria-expanded="false"
                        aria-controls="st-profile-menu"
                        aria-label="Profil dan pengaturan akun">
                    <span class="st-avatar" aria-hidden="true">
                        {{ $initials }}
                    </span>
                    <span class="st-profile-info">
                        <span class="st-profile-name">{{ $student->name }}</span>
                        @if ($firstClass)
                            <span class="st-profile-class">{{ $firstClass->name }}</span>
                        @else
                            <span class="st-profile-class">Murid</span>
                        @endif
                    </span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <ul id="st-profile-menu" class="st-dropdown st-profile-dropdown" hidden role="menu" aria-label="Menu Profil">
                    <li role="none">
                        <a href="{{ route('student.profile.edit') }}"
                           class="st-dropdown-item {{ request()->routeIs('student.profile.*') ? 'is-active' : '' }}"
                           role="menuitem">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Profil Saya
                        </a>
                    </li>
                    <li role="separator" style="height:1px;background:var(--st-border);margin:.375rem 0;"></li>
                    <li role="none">
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="st-dropdown-item" style="width:100%;text-align:left;border:none;background:none;cursor:pointer;font-family:inherit;color:#dc2626;" role="menuitem">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            {{-- Hamburger --}}
            <button type="button" id="st-hamburger" class="st-hamburger" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="st-drawer">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

{{-- Mobile Drawer --}}
<div id="st-drawer" class="st-mobile-drawer" role="dialog" aria-label="Menu Navigasi Mobile" aria-modal="true">
    <div class="st-mobile-drawer-header">
        <div class="st-brand" style="pointer-events:none;">
            <img src="{{ $institution['logo_url'] }}" alt="" width="36" height="36">
            <span class="st-brand-text">
                <span class="st-brand-name">{{ $institution['name'] }}</span>
                <span class="st-brand-label">Portal Murid</span>
            </span>
        </div>
        <button type="button" id="st-drawer-close" style="padding:.5rem;border:none;background:none;cursor:pointer;color:var(--st-muted);border-radius:.5rem;" aria-label="Tutup menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <nav aria-label="Menu Mobile">
        @foreach ($navItems as $item)
            @php
                $activePatterns = (array) ($item['active'] ?? []);
                $isActive = request()->routeIs(...$activePatterns);
            @endphp
            @isset($item['children'])
                <span class="st-mobile-section-label">{{ $item['label'] }}</span>
                @foreach ($item['children'] as $child)
                    @php $isChildActive = request()->routeIs($child['active']); @endphp
                    <a href="{{ route($child['route']) }}"
                       class="st-mobile-sub-link {{ $isChildActive ? 'is-active' : '' }}">
                        {{ $child['label'] }}
                    </a>
                @endforeach
            @else
                <a href="{{ route($item['route']) }}"
                   class="st-mobile-nav-link {{ $isActive ? 'is-active' : '' }}">
                    {{ $item['label'] }}
                    @if (($item['count'] ?? 0) > 0)
                        <span class="admin-count-badge" aria-hidden="true">{{ $item['count'] }}</span>
                    @endif
                </a>
            @endisset
        @endforeach

        <span class="st-mobile-section-label">Akun</span>
        <a href="{{ route('student.profile.edit') }}" class="st-mobile-nav-link {{ request()->routeIs('student.profile.*') ? 'is-active' : '' }}">Profil Saya</a>
        <a href="{{ route('notifications.index') }}" class="st-mobile-nav-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}">
            Notifikasi
            @if ($unreadNotifications > 0)
                <span class="admin-count-badge" aria-hidden="true">{{ $unreadNotifications }}</span>
            @endif
        </a>

        <div style="margin-top:1.5rem;">
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="st-btn st-btn-secondary st-btn-full" style="color:#dc2626;border-color:#fca5a5;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </nav>
</div>
