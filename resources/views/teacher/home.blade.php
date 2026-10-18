@extends('layouts.teacher')

@section('title', 'Beranda')

@php
$ongoingSession = $todaySessions->first(fn (array $session) => $session['status'] === \App\SessionStatus::Ongoing);
$upcomingSession = $todaySessions->first(fn (array $session) => $session['status'] === \App\SessionStatus::Upcoming);
$firstName = \Illuminate\Support\Str::of($teacher->name)->before(' ')->value() ?: $teacher->name;

if ($todaySessions->isEmpty()) {
    $summary = 'Hari ini tidak ada jadwal mengajar. Waktu yang pas untuk menyiapkan materi dan memeriksa tugas murid.';
} elseif ($ongoingSession !== null) {
    $summary = 'Kelas '.$ongoingSession['schedule']->classroom->name.' sedang berlangsung sampai pukul '.$ongoingSession['ends_at']->format('H:i').'. Semoga kelasnya menyenangkan.';
} elseif ($upcomingSession !== null) {
    $summary = 'Ada '.$todaySessions->count().' sesi mengajar hari ini. Kelas berikutnya pukul '.$upcomingSession['starts_at']->format('H:i').' di '.$upcomingSession['schedule']->classroom->name.'. Semoga lancar.';
} else {
    $summary = 'Semua sesi hari ini sudah selesai. Terima kasih sudah membimbing murid hari ini.';
}

$quickLinks = [
    ['label' => 'Jadwal Mengajar', 'icon' => 'schedule', 'route' => 'teacher.schedules.index'],
    ['label' => 'Kelas Saya',      'icon' => 'classes',  'route' => 'teacher.classes.index'],
    ['label' => 'Honorarium Saya', 'icon' => 'check',    'route' => 'teacher.honorariums.index'],
];
@endphp

@section('content')
    <x-admin.flash />

    <section class="teacher-hero" aria-labelledby="teacher-greeting" style="position:relative;overflow:hidden;">
        {{-- Dekorasi: bintang kecil --}}
        <span aria-hidden="true" class="th-deco-star" style="position:absolute;top:1.25rem;right:2.5rem;font-size:1rem;color:#FFC94A;line-height:1;pointer-events:none;">★</span>
        <span aria-hidden="true" class="th-deco-star" style="position:absolute;top:2.5rem;right:5rem;font-size:.625rem;color:#35D477;line-height:1;pointer-events:none;">★</span>
        <span aria-hidden="true" class="th-deco-star" style="position:absolute;bottom:1.75rem;left:1.5rem;font-size:.75rem;color:#B9F6D0;line-height:1;pointer-events:none;">★</span>

        {{-- Dekorasi: pesawat kertas --}}
        <span aria-hidden="true" class="th-deco-plane" style="position:absolute;top:1rem;left:45%;font-size:1.125rem;color:#079B58;opacity:.35;pointer-events:none;">✈</span>

        <div class="teacher-hero-text">
            <p class="teacher-hero-date">{{ $now->translatedFormat('l, d F Y') }}</p>
            <h1 id="teacher-greeting">{{ $greeting }}, {{ $firstName }}</h1>
            <p class="teacher-hero-summary">{{ $summary }}</p>
            <a href="{{ route('teacher.schedules.index') }}" class="admin-btn admin-btn-primary" id="btn-hero-schedule">Buka Jadwal Mengajar</a>
        </div>
        <figure class="teacher-hero-media" aria-hidden="true">
            <img src="{{ asset('images/guru.png') }}" alt="" width="1738" height="905" decoding="async" fetchpriority="high">
        </figure>
    </section>

    <section class="teacher-section" aria-labelledby="today-heading">
        <div class="teacher-section-head">
            <div>
                <h2 id="today-heading">Jadwal Mengajar Hari Ini</h2>
                <p>Check-in dibuka {{ \App\Models\TeacherAttendance::CHECK_IN_OPENS_MINUTES_BEFORE }} menit sebelum kelas dimulai dan dicatat dengan waktu server.</p>
            </div>
            <a href="{{ route('teacher.schedules.index') }}" class="teacher-link">Lihat Semua Jadwal</a>
        </div>

        @if ($todaySessions->isEmpty())
            <div class="teacher-free-day">
                <x-admin.icon name="schedule" :size="28" />
                <div>
                    <h3>Tidak ada kelas hari ini</h3>
                    @if ($nextSession !== null)
                        <p>Kelas berikutnya: <strong>{{ $nextSession['schedule']->classroom->name }}</strong>, {{ $nextSession['starts_at']->translatedFormat('l, d F Y') }} pukul {{ $nextSession['starts_at']->format('H:i') }}.</p>
                    @else
                        <p>Belum ada jadwal aktif untuk kelas Anda. Jadwal dibuat oleh admin dan akan muncul di sini.</p>
                    @endif
                </div>
                <a href="{{ route('teacher.schedules.index') }}" class="admin-btn admin-btn-secondary admin-btn-sm">Lihat jadwal minggu ini</a>
            </div>
        @else
            <ul class="teacher-sessions">
                @foreach ($todaySessions as $session)
                    @include('teacher.partials.session', ['session' => $session, 'showDate' => false])
                @endforeach
            </ul>
        @endif
    </section>

    <section class="teacher-section" aria-labelledby="quick-heading">
        <div class="teacher-section-head">
            <h2 id="quick-heading">Akses Cepat</h2>
        </div>
        <ul class="teacher-quick">
            @foreach ($quickLinks as $link)
                <li><a href="{{ route($link['route']) }}"><x-admin.icon :name="$link['icon']" :size="18" /> {{ $link['label'] }}</a></li>
            @endforeach
        </ul>
    </section>

    <section class="teacher-section" aria-labelledby="activity-heading">
        <div class="teacher-section-head">
            <h2 id="activity-heading">Aktivitas Terbaru</h2>
        </div>

        <div class="teacher-activity">
            <section class="admin-panel" aria-labelledby="recent-materials-heading">
                <div class="admin-panel-header">
                    <div><h3 id="recent-materials-heading">Materi Terbaru</h3></div>
                </div>
                @if ($recentMaterials->isEmpty())
                    <x-admin.empty-state title="Belum ada materi" icon="learning">Buka halaman kelas untuk menambahkan materi belajar.</x-admin.empty-state>
                @else
                    <ul class="teacher-list">
                        @foreach ($recentMaterials as $material)
                            <li class="teacher-list-item">
                                <div>
                                    <a href="{{ route('teacher.learning.materials.show', $material) }}">{{ $material->title }}</a>
                                    <p>{{ $material->classroom->name }} · {{ $material->published_at?->translatedFormat('d M Y') ?? 'Draft' }}</p>
                                </div>
                                <span class="admin-badge {{ $material->status->badgeClass() }}" style="flex-shrink:0;">{{ $material->status->label() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="admin-panel" aria-labelledby="notifications-heading">
                <div class="admin-panel-header">
                    <div><h3 id="notifications-heading">Notifikasi</h3></div>
                    <a href="{{ route('notifications.index') }}" class="teacher-link">Semua</a>
                </div>
                @if ($recentNotifications->isEmpty())
                    <x-admin.empty-state title="Belum ada notifikasi" icon="bell">Notifikasi muncul saat ada jadwal baru atau pengumuman dari admin.</x-admin.empty-state>
                @else
                    <ul class="teacher-list">
                        @foreach ($recentNotifications as $notification)
                            <li class="teacher-list-item {{ $notification->read_at === null ? 'is-unread' : '' }}">
                                <div>
                                    <a href="{{ route('notifications.open', $notification->id) }}">
                                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                                        @if ($notification->read_at === null)<span class="sr-only">(belum dibaca)</span>@endif
                                    </a>
                                    <p>{{ \Illuminate\Support\Str::limit($notification->data['message'] ?? '', 80) }} · {{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </section>
@endsection
