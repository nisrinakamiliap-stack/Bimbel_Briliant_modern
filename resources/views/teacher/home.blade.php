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
    ['label' => 'Kelas Saya', 'icon' => 'classes', 'route' => 'teacher.classes.index'],
    ['label' => 'Materi Pembelajaran', 'icon' => 'learning', 'route' => 'teacher.learning.materials.index'],
    ['label' => 'Tugas', 'icon' => 'edit', 'route' => 'teacher.learning.assignments.index'],
    ['label' => 'Pengumpulan Tugas', 'icon' => 'inbox', 'route' => 'teacher.learning.submissions.index'],
    ['label' => 'Penilaian', 'icon' => 'check', 'route' => 'teacher.learning.grades.index'],
    ['label' => 'Absensi', 'icon' => 'attendance', 'route' => 'teacher.attendances.index'],
    ['label' => 'Pesan', 'icon' => 'message', 'route' => 'messages.index'],
];
@endphp

@section('content')
    <x-admin.flash />

    <section class="teacher-hero" aria-labelledby="teacher-greeting">
        <div class="teacher-hero-text">
            <p class="teacher-hero-date">{{ $now->translatedFormat('l, d F Y') }}</p>
            <h1 id="teacher-greeting">{{ $greeting }}, {{ $firstName }}</h1>
            <p class="teacher-hero-summary">{{ $summary }}</p>
            <a href="{{ route('teacher.schedules.index') }}" class="admin-btn admin-btn-primary" id="btn-hero-schedule">Buka Jadwal Mengajar</a>
        </div>
        <figure class="teacher-hero-media">
            <img src="{{ asset('images/guru.png') }}" alt="Ilustrasi guru menjelaskan pelajaran di depan papan tulis" width="1738" height="905" decoding="async" fetchpriority="high">
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
            <div>
                <h2 id="activity-heading">Aktivitas Penting</h2>
                <p>{{ $ungradedCount > 0 ? $ungradedCount.' pengumpulan belum diberi nilai.' : 'Tidak ada pengumpulan yang menunggu nilai.' }}</p>
            </div>
        </div>

        <div class="teacher-activity">
            <section class="admin-panel" aria-labelledby="review-heading">
                <div class="admin-panel-header">
                    <div><h3 id="review-heading">Tugas Perlu Diperiksa</h3></div>
                    @if ($ungradedCount > 0)
                        <a href="{{ route('teacher.learning.grades.index') }}" class="teacher-link">Buka Penilaian</a>
                    @endif
                </div>
                @if ($assignmentsToReview->isEmpty())
                    <x-admin.empty-state title="Semua sudah dinilai" icon="check">Tugas yang memiliki pengumpulan belum dinilai akan muncul di sini.</x-admin.empty-state>
                @else
                    <ul class="teacher-list">
                        @foreach ($assignmentsToReview as $assignment)
                            <li class="teacher-list-item">
                                <div>
                                    <a href="{{ route('teacher.learning.grades.index', ['tugas' => $assignment->id]) }}">{{ $assignment->title }}</a>
                                    <p>{{ $assignment->classroom->name }} · tenggat {{ $assignment->due_at->translatedFormat('d M, H:i') }}</p>
                                </div>
                                <span class="admin-badge is-pending" style="flex-shrink: 0;">{{ $assignment->ungraded_count }} belum dinilai</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="admin-panel" aria-labelledby="recent-submissions-heading">
                <div class="admin-panel-header">
                    <div><h3 id="recent-submissions-heading">Pengumpulan Terbaru</h3></div>
                    <a href="{{ route('teacher.learning.submissions.index') }}" class="teacher-link">Semua</a>
                </div>
                @if ($recentSubmissions->isEmpty())
                    <x-admin.empty-state title="Belum ada pengumpulan" icon="inbox">Jawaban murid akan tampil di sini setelah tugas diterbitkan.</x-admin.empty-state>
                @else
                    <ul class="teacher-list">
                        @foreach ($recentSubmissions as $submission)
                            <li class="teacher-list-item">
                                <div>
                                    <a href="{{ route('teacher.learning.submissions.show', $submission) }}">{{ $submission->student->name }}</a>
                                    <p>{{ $submission->assignment->title }} · {{ $submission->submitted_at->diffForHumans() }}</p>
                                </div>
                                <span class="admin-badge {{ $submission->statusBadgeClass() }}" style="flex-shrink: 0;">{{ $submission->statusLabel() }}</span>
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
                    <x-admin.empty-state title="Belum ada notifikasi" icon="bell">Notifikasi muncul saat ada jadwal baru, pengumpulan tugas, atau pengumuman dari admin.</x-admin.empty-state>
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
