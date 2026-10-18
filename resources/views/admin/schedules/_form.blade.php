{{--
    Field form jadwal yang dipakai bersama oleh halaman tambah dan edit.

    @var \App\Models\Schedule|null $schedule
    @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
    @var array<int, \App\Weekday> $weekdays
    @var int|null $preselectedClassroomId
--}}
@php
$fieldClass = fn (string $field, string $base = 'admin-input'): string => $base.($errors->has($field) ? ' is-invalid' : '');
$describedBy = fn (string $field, bool $hasHint = false): string => collect([
    $hasHint ? $field.'-hint' : null,
    $errors->has($field) ? $field.'-error' : null,
])->filter()->implode(' ');

$selectedClassroomId = (string) old('classroom_id', $schedule?->classroom_id ?? $preselectedClassroomId);
$selectedDay = (string) old('day_of_week', $schedule?->day_of_week?->value);
$isActiveValue = (string) old('is_active', $schedule === null || $schedule->is_active ? '1' : '0');
$hasClassWithoutTeacher = $classrooms->contains(fn ($classroom) => $classroom->teacher_id === null);
@endphp

<div class="admin-form-section">
    <h3>Kelas &amp; Waktu</h3>
    <p>Guru pengajar mengikuti guru yang ditetapkan pada kelas.</p>

    <div class="admin-form-grid">
        <div class="admin-field is-full">
            <label for="classroom_id" class="admin-label">Kelas <span class="is-required" aria-hidden="true">*</span></label>
            <select id="classroom_id" name="classroom_id" class="{{ $fieldClass('classroom_id', 'admin-select') }}" required
                    @disabled($classrooms->isEmpty())
                    @error('classroom_id') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('classroom_id', true) }}">
                <option value="">— Pilih kelas —</option>
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected($selectedClassroomId === (string) $classroom->id)>
                        {{ $classroom->name }} — {{ $classroom->program->name }} — Guru: {{ $classroom->teacher?->name ?? 'belum ditentukan' }}{{ $classroom->is_active ? '' : ' (kelas tidak aktif)' }}
                    </option>
                @endforeach
            </select>
            <p id="classroom_id-hint" class="admin-hint">
                @if ($hasClassWithoutTeacher)
                    Kelas tanpa guru tetap dapat dijadwalkan; tetapkan guru melalui menu Kelas agar bentrok jadwal guru ikut diperiksa.
                @else
                    Bentrok jadwal kelas dan guru diperiksa otomatis saat disimpan.
                @endif
            </p>
            @error('classroom_id') <p id="classroom_id-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="day_of_week" class="admin-label">Hari <span class="is-required" aria-hidden="true">*</span></label>
            <select id="day_of_week" name="day_of_week" class="{{ $fieldClass('day_of_week', 'admin-select') }}" required
                    @error('day_of_week') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('day_of_week') }}">
                <option value="">— Pilih hari —</option>
                @foreach ($weekdays as $weekday)
                    <option value="{{ $weekday->value }}" @selected($selectedDay === (string) $weekday->value)>{{ $weekday->label() }}</option>
                @endforeach
            </select>
            @error('day_of_week') <p id="day_of_week-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="room" class="admin-label">Ruangan / Lokasi <span class="is-optional">(opsional)</span></label>
            <input type="text" id="room" name="room" class="{{ $fieldClass('room') }}"
                   value="{{ old('room', $schedule?->room) }}" maxlength="100" placeholder="Contoh: Ruang 3 atau Zoom"
                   @error('room') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('room') }}">
            @error('room') <p id="room-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="start_time" class="admin-label">Jam Mulai <span class="is-required" aria-hidden="true">*</span></label>
            <input type="time" id="start_time" name="start_time" class="{{ $fieldClass('start_time') }}"
                   value="{{ old('start_time', $schedule ? substr($schedule->start_time, 0, 5) : '') }}" required step="60"
                   @error('start_time') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('start_time') }}">
            @error('start_time') <p id="start_time-error" class="admin-error" role="alert">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="end_time" class="admin-label">Jam Selesai <span class="is-required" aria-hidden="true">*</span></label>
            <input type="time" id="end_time" name="end_time" class="{{ $fieldClass('end_time') }}"
                   value="{{ old('end_time', $schedule ? substr($schedule->end_time, 0, 5) : '') }}" required step="60"
                   @error('end_time') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('end_time') }}">
            @error('end_time') <p id="end_time-error" class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3>Masa Berlaku &amp; Status</h3>
    <p>Kosongkan tanggal jika jadwal berlaku tanpa batas waktu.</p>

    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="starts_on" class="admin-label">Tanggal Mulai Berlaku <span class="is-optional">(opsional)</span></label>
            <input type="date" id="starts_on" name="starts_on" class="{{ $fieldClass('starts_on') }}"
                   value="{{ old('starts_on', $schedule?->starts_on?->format('Y-m-d')) }}"
                   @error('starts_on') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('starts_on') }}">
            @error('starts_on') <p id="starts_on-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="ends_on" class="admin-label">Tanggal Berakhir <span class="is-optional">(opsional)</span></label>
            <input type="date" id="ends_on" name="ends_on" class="{{ $fieldClass('ends_on') }}"
                   value="{{ old('ends_on', $schedule?->ends_on?->format('Y-m-d')) }}"
                   @error('ends_on') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('ends_on') }}">
            @error('ends_on') <p id="ends_on-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="is_active" class="admin-label">Status Jadwal <span class="is-required" aria-hidden="true">*</span></label>
            <select id="is_active" name="is_active" class="{{ $fieldClass('is_active', 'admin-select') }}" required
                    @error('is_active') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('is_active', true) }}">
                <option value="1" @selected($isActiveValue === '1')>Aktif</option>
                <option value="0" @selected($isActiveValue === '0')>Tidak Aktif</option>
            </select>
            <p id="is_active-hint" class="admin-hint">Jadwal tidak aktif tidak diperiksa bentroknya dan tidak tampil sebagai jadwal mendatang.</p>
            @error('is_active') <p id="is_active-error" class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
