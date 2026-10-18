@extends('layouts.teacher')

@section('title', 'Jadwal Mengajar')

@php
$query = fn (array $overrides) => array_filter([
    'tampilan' => $view,
    'tanggal' => $date->format('Y-m-d'),
    'kelas' => $classroomId,
    ...$overrides,
]);
$periodLabel = $view === 'harian'
    ? $date->translatedFormat('l, d F Y')
    : $from->translatedFormat('d M').' sampai '.$to->translatedFormat('d M Y');
@endphp

@section('content')
    <x-admin.flash />

    <div class="teacher-page-head">
        <div>
            <h1>Jadwal Mengajar</h1>
            <p>Jadwal kelas yang Anda ampu. Check-in dibuka {{ \App\Models\TeacherAttendance::CHECK_IN_OPENS_MINUTES_BEFORE }} menit sebelum kelas dimulai, dan waktu dicatat oleh server.</p>
        </div>
    </div>

    @include('teacher.partials.class-tabs')

    <section class="admin-panel" aria-labelledby="schedule-filter-heading" style="margin-bottom: 1.25rem;">
        <h2 id="schedule-filter-heading" class="sr-only">Filter jadwal</h2>
        <form action="{{ route('teacher.schedules.index') }}" method="GET" class="admin-toolbar is-stacked">
            <input type="hidden" name="tampilan" value="{{ $view }}">
            <div class="admin-toolbar-row">
                <nav class="admin-view-switch" aria-label="Tampilan jadwal">
                    <a href="{{ route('teacher.schedules.index', $query(['tampilan' => 'harian'])) }}" class="{{ $view === 'harian' ? 'is-active' : '' }}" @if ($view === 'harian') aria-current="page" @endif>Harian</a>
                    <a href="{{ route('teacher.schedules.index', $query(['tampilan' => 'mingguan'])) }}" class="{{ $view === 'mingguan' ? 'is-active' : '' }}" @if ($view === 'mingguan') aria-current="page" @endif>Mingguan</a>
                </nav>
                <label for="tanggal" class="sr-only">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ $date->format('Y-m-d') }}" class="admin-input" style="flex: 1; min-width: 10rem;">
                <label for="kelas" class="sr-only">Kelas</label>
                <select id="kelas" name="kelas" class="admin-select">
                    <option value="">Semua kelas</option>
                    @foreach ($classrooms as $classroomOption)
                        <option value="{{ $classroomOption->id }}" @selected($classroomId === $classroomOption->id)>{{ $classroomOption->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
            </div>
            @error('kelas') <p class="admin-error" style="margin: 0;">{{ $message }}</p> @enderror
            @error('tanggal') <p class="admin-error" style="margin: 0;">{{ $message }}</p> @enderror
        </form>
    </section>

    <div class="teacher-section-head">
        <h2 id="period-heading">{{ $periodLabel }}</h2>
        <nav class="admin-actions" aria-label="Pindah periode">
            <a href="{{ route('teacher.schedules.index', $query(['tanggal' => $previousDate->format('Y-m-d')])) }}" class="admin-btn admin-btn-secondary admin-btn-sm">{{ $view === 'harian' ? 'Hari sebelumnya' : 'Minggu sebelumnya' }}</a>
            <a href="{{ route('teacher.schedules.index', $query(['tanggal' => now()->format('Y-m-d')])) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Hari ini</a>
            <a href="{{ route('teacher.schedules.index', $query(['tanggal' => $nextDate->format('Y-m-d')])) }}" class="admin-btn admin-btn-secondary admin-btn-sm">{{ $view === 'harian' ? 'Hari berikutnya' : 'Minggu berikutnya' }}</a>
        </nav>
    </div>

    @if ($classrooms->isEmpty())
        <div class="admin-panel">
            <x-admin.empty-state title="Anda belum mengampu kelas" icon="classes">
                Jadwal mengajar muncul setelah admin menugaskan Anda sebagai guru sebuah kelas.
            </x-admin.empty-state>
        </div>
    @elseif ($view === 'harian')
        @if ($sessions->isEmpty())
            <div class="admin-panel">
                <x-admin.empty-state title="Tidak ada jadwal pada hari ini" icon="schedule">
                    Pilih tanggal lain atau lihat tampilan mingguan untuk melihat jadwal Anda yang lain.
                </x-admin.empty-state>
            </div>
        @else
            <ul class="teacher-sessions" aria-labelledby="period-heading">
                @foreach ($sessions as $session)
                    @include('teacher.partials.session', ['session' => $session, 'showDate' => false])
                @endforeach
            </ul>
        @endif
    @else
        @for ($day = $from; $day->lessThanOrEqualTo($to); $day = $day->addDay())
            @php $daySessions = $sessionsByDay->get($day->format('Y-m-d'), collect()); @endphp
            <h3 class="teacher-day-label">
                {{ $day->translatedFormat('l') }}<span>{{ $day->translatedFormat('d F') }}{{ $day->isToday() ? ', hari ini' : '' }}</span>
            </h3>
            @if ($daySessions->isEmpty())
                <p class="teacher-day-empty">Tidak ada jadwal.</p>
            @else
                <ul class="teacher-sessions">
                    @foreach ($daySessions as $session)
                        @include('teacher.partials.session', ['session' => $session, 'showDate' => false])
                    @endforeach
                </ul>
            @endif
        @endfor
    @endif
@endsection
