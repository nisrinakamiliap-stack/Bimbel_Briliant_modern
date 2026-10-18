{{-- Navbar Portal Guru. Menu aktif mengikuti route; seluruh tujuan adalah route yang tersedia. --}}
@php
$teacher = auth()->user();
$initials = collect(preg_split('/\s+/', trim($teacher->name)))->filter()->take(2)->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
$learningLinks = [
    ['label' => 'Materi Pembelajaran', 'hint' => 'Bahan ajar untuk kelas Anda', 'route' => 'teacher.learning.materials.index', 'active' => 'teacher.learning.materials.*'],
    ['label' => 'Tugas', 'hint' => 'Buat, terbitkan, dan arsipkan tugas', 'route' => 'teacher.learning.assignments.index', 'active' => 'teacher.learning.assignments.*'],
    ['label' => 'Pengumpulan Tugas', 'hint' => 'Jawaban yang masuk dari murid', 'route' => 'teacher.learning.submissions.index', 'active' => 'teacher.learning.submissions.*'],
    ['label' => 'Penilaian Tugas', 'hint' => 'Pengumpulan yang menunggu nilai', 'route' => 'teacher.learning.grades.index', 'active' => 'teacher.learning.grades.*'],
];
$navItems = [
    ['label' => 'Beranda', 'route' => 'teacher.dashboard', 'active' => 'teacher.dashboard'],
    ['label' => 'Kelas & Jadwal', 'route' => 'teacher.schedules.index', 'active' => ['teacher.schedules.*', 'teacher.classes.*']],
    ['label' => 'Pembelajaran', 'children' => $learningLinks, 'active' => 'teacher.learning.*'],
    ['label' => 'Absensi', 'route' => 'teacher.attendances.index', 'active' => 'teacher.attendances.*'],
    ['label' => 'Honorarium Saya', 'route' => 'teacher.honorariums.index', 'active' => 'teacher.honorariums.*'],
];
// Pesan tidak ada di navbar; aksesnya melalui tombol mengambang (teacher.partials.message-button).
$unreadNotifications = $navCounts['notifications'] ?? 0;
@endphp

<header class="teacher-nav">
    <div class="teacher-container teacher-nav-inner">
        <a href="{{ route('teacher.dashboard') }}" class="teacher-brand" aria-label="{{ $institution['name'] }}, Portal Guru: Beranda">
            <img src="{{ $institution['logo_url'] }}" alt="" width="36" height="36">
            <span class="teacher-brand-text">
                <span class="teacher-brand-name">{{ $institution['name'] }}</span>
                <span class="teacher-brand-label">Portal Guru</span>
            </span>
        </a>

        <nav aria-label="Menu Portal Guru">
            <ul class="teacher-nav-links">
                @foreach ($navItems as $item)
                    @php $isActive = request()->routeIs(...(array) $item['active']); @endphp
                    <li @class(['teacher-dropdown-wrap' => isset($item['children'])])>
                        @isset($item['children'])
                            <button type="button" id="nav-learning-toggle" class="teacher-nav-link {{ $isActive ? 'is-active' : '' }}"
                                    aria-expanded="false" aria-controls="nav-learning-menu" data-dropdown-toggle>
                                {{ $item['label'] }}
                                <x-admin.icon name="chevron-down" :size="16" />
                            </button>
                            <ul id="nav-learning-menu" class="teacher-dropdown" hidden>
                                @foreach ($item['children'] as $child)
                                    @php $isChildActive = request()->routeIs($child['active']); @endphp
                                    <li>
                                        <a href="{{ route($child['route']) }}" class="teacher-dropdown-item {{ $isChildActive ? 'is-active' : '' }}" @if ($isChildActive) aria-current="page" @endif>
                                            {{ $child['label'] }}
                                            <small>{{ $child['hint'] }}</small>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <a href="{{ route($item['route']) }}" class="teacher-nav-link {{ $isActive ? 'is-active' : '' }}" @if ($isActive) aria-current="page" @endif>
                                {{ $item['label'] }}
                                @if (($item['count'] ?? 0) > 0)
                                    <span class="admin-count-badge" aria-label="{{ $item['count'] }} percakapan belum dibaca">{{ $item['count'] }}</span>
                                @endif
                            </a>
                        @endisset
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="teacher-nav-actions">
            <a href="{{ route('notifications.index') }}" id="teacher-notifications"
               class="teacher-icon-button {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}"
               aria-label="Notifikasi{{ $unreadNotifications > 0 ? ", {$unreadNotifications} belum dibaca" : '' }}"
               @if (request()->routeIs('notifications.*')) aria-current="page" @endif>
                <x-admin.icon name="bell" />
                @if ($unreadNotifications > 0)
                    <span class="admin-count-badge" aria-hidden="true">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                @endif
            </a>

            {{-- Di bawah 1024px Profil Saya dan Keluar tersedia di menu seluler, jadi tombol ini disembunyikan. --}}
            <div class="teacher-dropdown-wrap teacher-profile-wrap">
                <button type="button" id="teacher-profile-toggle" class="teacher-icon-button {{ request()->routeIs('teacher.profile.*') ? 'is-active' : '' }}"
                        aria-expanded="false" aria-controls="teacher-profile-menu" aria-label="Menu akun {{ $teacher->name }}" data-dropdown-toggle>
                    <span class="teacher-avatar" aria-hidden="true">{{ $initials }}</span>
                    <span class="teacher-profile-name">{{ $teacher->name }}</span>
                    <x-admin.icon name="chevron-down" :size="16" />
                </button>
                <div id="teacher-profile-menu" class="teacher-dropdown is-right" hidden>
                    <div class="teacher-dropdown-header">
                        <strong>{{ $teacher->name }}</strong>
                        {{ $teacher->email }}
                    </div>
                    <div class="teacher-dropdown-divider"></div>
                    <a href="{{ route('teacher.profile.edit') }}" class="teacher-dropdown-item {{ request()->routeIs('teacher.profile.*') ? 'is-active' : '' }}">Profil Saya</a>
                    <a href="{{ route('teacher.honorariums.index') }}" class="teacher-dropdown-item {{ request()->routeIs('teacher.honorariums.*') ? 'is-active' : '' }}">Honorarium Saya</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="teacher-dropdown-item">Keluar</button>
                    </form>
                </div>
            </div>

            <button type="button" id="teacher-menu-toggle" class="teacher-icon-button teacher-menu-toggle" aria-expanded="false" aria-controls="teacher-mobile-menu">
                <x-admin.icon name="menu" />
                <span data-menu-label>Menu</span>
            </button>
        </div>
    </div>

    <div id="teacher-mobile-menu" class="teacher-mobile-menu" hidden>
        <nav class="teacher-container" aria-label="Menu Portal Guru (seluler)">
            <ul class="teacher-mobile-list">
                @foreach ($navItems as $item)
                    @isset($item['children'])
                        <li class="teacher-mobile-group">{{ $item['label'] }}</li>
                        @foreach ($item['children'] as $child)
                            @php $isChildActive = request()->routeIs($child['active']); @endphp
                            <li><a href="{{ route($child['route']) }}" class="teacher-mobile-link is-sub {{ $isChildActive ? 'is-active' : '' }}" @if ($isChildActive) aria-current="page" @endif>{{ $child['label'] }}</a></li>
                        @endforeach
                    @else
                        @php $isActive = request()->routeIs(...(array) $item['active']); @endphp
                        <li>
                            <a href="{{ route($item['route']) }}" class="teacher-mobile-link {{ $isActive ? 'is-active' : '' }}" @if ($isActive) aria-current="page" @endif>
                                {{ $item['label'] }}
                                @if (($item['count'] ?? 0) > 0)
                                    <span class="admin-count-badge">{{ $item['count'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endisset
                @endforeach
                <li class="teacher-mobile-group">Akun</li>
                <li><a href="{{ route('teacher.profile.edit') }}" class="teacher-mobile-link is-sub {{ request()->routeIs('teacher.profile.*') ? 'is-active' : '' }}">Profil Saya</a></li>
                <li><a href="{{ route('teacher.honorariums.index') }}" class="teacher-mobile-link is-sub {{ request()->routeIs('teacher.honorariums.*') ? 'is-active' : '' }}">Honorarium Saya</a></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="teacher-mobile-link is-sub">Keluar</button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</header>
