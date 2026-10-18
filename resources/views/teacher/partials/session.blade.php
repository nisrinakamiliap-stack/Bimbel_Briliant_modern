{{--
    Satu sesi mengajar pada rel waktu.
    @var array{schedule: \App\Models\Schedule, date: \Carbon\CarbonImmutable, starts_at: \Carbon\CarbonImmutable, ends_at: \Carbon\CarbonImmutable, status: \App\SessionStatus, attendance: ?\App\Models\TeacherAttendance, can_check_in: bool, can_check_out: bool} $session
    @var bool $showDate
--}}
@php
$schedule = $session['schedule'];
$classroom = $schedule->classroom;
$attendance = $session['attendance'];
$isOngoing = $session['status'] === \App\SessionStatus::Ongoing;
$isToday = $session['date']->isToday();
$checkInOpensAt = $session['starts_at']->subMinutes(\App\Models\TeacherAttendance::CHECK_IN_OPENS_MINUTES_BEFORE);
$hasStarted = $session['status'] !== \App\SessionStatus::Upcoming;
@endphp
<li class="teacher-session {{ $isOngoing ? 'is-ongoing' : '' }}">
    <div class="teacher-session-time">
        <strong>{{ $session['starts_at']->format('H:i') }}</strong>
        <span>{{ $session['ends_at']->format('H:i') }}</span>
    </div>

    <div class="teacher-session-body">
        <h3><a href="{{ route('teacher.classes.show', $classroom) }}">{{ $classroom->name }}</a></h3>
        <p class="teacher-session-meta">
            {{ $classroom->program->name }} · {{ $classroom->student_count ?? 0 }} murid · {{ $schedule->room ?: 'Ruangan belum ditentukan' }}
            @if ($showDate ?? false)
                · {{ $session['date']->translatedFormat('l, d F Y') }}
            @endif
        </p>
        <div class="teacher-session-state">
            <span class="admin-badge {{ $session['status']->badgeClass() }}">{{ $session['status']->label() }}</span>
            @if ($attendance !== null)
                <span>
                    Check-in {{ $attendance->checked_in_at->format('H:i') }} ({{ $attendance->status->label() }})
                    @if ($attendance->checked_out_at !== null)
                        · Check-out {{ $attendance->checked_out_at->format('H:i') }}
                    @endif
                </span>
            @elseif ($isToday && ! $session['can_check_in'] && $session['status'] === \App\SessionStatus::Upcoming)
                <span>Check-in dibuka pukul {{ $checkInOpensAt->format('H:i') }}</span>
            @elseif ($session['status'] === \App\SessionStatus::Finished)
                <span>Tidak ada check-in tercatat</span>
            @endif
        </div>
    </div>

    <div class="teacher-session-actions">
        @if ($session['can_check_in'])
            <form action="{{ route('teacher.attendance.check-in', $schedule) }}" method="POST">
                @csrf
                <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm" data-loading-label="Menyimpan...">Check-in Sekarang</button>
            </form>
        @endif
        @if ($session['can_check_out'])
            <form action="{{ route('teacher.attendance.check-out', $schedule) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="admin-btn admin-btn-secondary admin-btn-sm" data-loading-label="Menyimpan...">Check-out</button>
            </form>
        @endif
        @if ($hasStarted)
            <a href="{{ route('teacher.attendances.create', ['tanggal' => $session['date']->format('Y-m-d'), 'jadwal' => $schedule->id]) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Absensi Murid</a>
        @endif
        <a href="{{ route('teacher.classes.show', $classroom) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Detail kelas {{ $classroom->name }}">Detail</a>
    </div>
</li>
