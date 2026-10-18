@extends('layouts.teacher')

@section('title', 'Kelas Saya')

@section('content')
    <x-admin.flash />

    <div class="teacher-page-head">
        <div>
            <h1>Kelas Saya</h1>
            <p>Kelas yang ditugaskan admin kepada Anda, beserta jumlah murid dan jadwal belajarnya.</p>
        </div>
    </div>

    @include('teacher.partials.class-tabs')

    @if ($classrooms->isEmpty())
        <div class="admin-panel">
            <x-admin.empty-state title="Anda belum mengampu kelas" icon="classes">
                Kelas akan tampil di sini setelah admin menugaskan Anda sebagai guru sebuah kelas.
            </x-admin.empty-state>
        </div>
    @else
        <div class="teacher-class-grid">
            @foreach ($classrooms as $classroom)
                <article class="teacher-class" aria-labelledby="class-{{ $classroom->id }}">
                    <div style="display: flex; justify-content: space-between; gap: .75rem; align-items: flex-start;">
                        <h2 id="class-{{ $classroom->id }}">{{ $classroom->name }}</h2>
                        @unless ($classroom->is_active)
                            <span class="admin-badge is-neutral">Nonaktif</span>
                        @endunless
                    </div>
                    <p class="teacher-class-program">{{ $classroom->program->name }} · {{ $classroom->learning_mode->label() }}</p>
                    <ul class="teacher-class-facts">
                        <li><x-admin.icon name="students" :size="16" /> {{ $classroom->student_count }} murid terdaftar (kapasitas {{ $classroom->capacity }})</li>
                        <li>
                            <x-admin.icon name="schedule" :size="16" />
                            <span>
                                @forelse ($classroom->schedules as $schedule)
                                    {{ $schedule->summary() }}{{ $schedule->room ? ' · '.$schedule->room : '' }}@if (! $loop->last)<br>@endif
                                @empty
                                    Belum ada jadwal aktif
                                @endforelse
                            </span>
                        </li>
                    </ul>
                    <a href="{{ route('teacher.classes.show', $classroom) }}" class="admin-btn admin-btn-secondary admin-btn-sm" aria-label="Lihat detail {{ $classroom->name }}">Lihat Detail</a>
                </article>
            @endforeach
        </div>
    @endif
@endsection
