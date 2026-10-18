{{--
    Field form kelas yang dipakai bersama oleh halaman tambah dan edit.

    @var \App\Models\Classroom|null $classroom
    @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Program> $programs
    @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $teachers
    @var array<int, \App\LearningMode> $learningModes
--}}
@php
$fieldClass = fn (string $field, string $base = 'admin-input'): string => $base.($errors->has($field) ? ' is-invalid' : '');
$describedBy = fn (string $field, bool $hasHint = false): string => collect([
    $hasHint ? $field.'-hint' : null,
    $errors->has($field) ? $field.'-error' : null,
])->filter()->implode(' ');

$selectedProgramId = (string) old('program_id', $classroom?->program_id);
$selectedTeacherId = (string) old('teacher_id', $classroom?->teacher_id);
$selectedMode = old('learning_mode', $classroom?->learning_mode?->value ?? 'offline');
$isActiveValue = (string) old('is_active', $classroom === null || $classroom->is_active ? '1' : '0');
@endphp

<div class="admin-form-section">
    <h3>Informasi Kelas</h3>
    <p>Nama, kode, dan program bimbel tempat kelas ini berada.</p>

    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="name" class="admin-label">Nama Kelas <span class="is-required" aria-hidden="true">*</span></label>
            <input type="text" id="name" name="name" class="{{ $fieldClass('name') }}"
                   value="{{ old('name', $classroom?->name) }}" maxlength="150" required placeholder="Contoh: SMA Kelas 12 IPA - Reguler"
                   @error('name') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('name') }}">
            @error('name') <p id="name-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="code" class="admin-label">Kode Kelas <span class="is-optional">(opsional)</span></label>
            <input type="text" id="code" name="code" class="{{ $fieldClass('code') }}" style="text-transform: uppercase;"
                   value="{{ old('code', $classroom?->code) }}" maxlength="30" placeholder="Contoh: SMA-IPA-01"
                   @error('code') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('code', true) }}">
            <p id="code-hint" class="admin-hint">Huruf, angka, dan tanda hubung. Harus unik antar kelas.</p>
            @error('code') <p id="code-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="program_id" class="admin-label">Program Bimbel <span class="is-required" aria-hidden="true">*</span></label>
            <select id="program_id" name="program_id" class="{{ $fieldClass('program_id', 'admin-select') }}" required
                    @disabled($programs->isEmpty())
                    @error('program_id') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('program_id') }}">
                <option value="">— Pilih program —</option>
                @foreach ($programs as $program)
                    <option value="{{ $program->id }}" @selected($selectedProgramId === (string) $program->id)>
                        {{ $program->name }}{{ $program->is_active ? '' : ' (tidak aktif)' }}
                    </option>
                @endforeach
            </select>
            @error('program_id') <p id="program_id-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="teacher_id" class="admin-label">Guru Pengajar <span class="is-optional">(opsional)</span></label>
            <select id="teacher_id" name="teacher_id" class="{{ $fieldClass('teacher_id', 'admin-select') }}"
                    @error('teacher_id') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('teacher_id', true) }}">
                <option value="">— Belum ditentukan —</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected($selectedTeacherId === (string) $teacher->id)>
                        {{ $teacher->name }}{{ $teacher->teacherProfile?->subject ? ' — '.$teacher->teacherProfile->subject : '' }}{{ $teacher->trashed() ? ' (akun dihapus)' : '' }}
                    </option>
                @endforeach
            </select>
            <p id="teacher_id-hint" class="admin-hint">
                @if ($teachers->isEmpty())
                    Belum ada akun guru. <a href="{{ route('admin.teachers.create') }}" style="color: var(--admin-primary);">Tambah guru</a> terlebih dahulu.
                @else
                    Guru dapat ditentukan nanti.
                @endif
            </p>
            @error('teacher_id') <p id="teacher_id-error" class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3>Pengaturan Kelas</h3>
    <p>Kapasitas, mode pembelajaran, dan status kelas.</p>

    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="capacity" class="admin-label">Kapasitas Maksimal Murid <span class="is-required" aria-hidden="true">*</span></label>
            <input type="number" id="capacity" name="capacity" class="{{ $fieldClass('capacity') }}"
                   value="{{ old('capacity', $classroom?->capacity) }}" min="1" max="500" step="1" required placeholder="Contoh: 20"
                   @error('capacity') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('capacity') }}">
            @error('capacity') <p id="capacity-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="learning_mode" class="admin-label">Mode Pembelajaran <span class="is-required" aria-hidden="true">*</span></label>
            <select id="learning_mode" name="learning_mode" class="{{ $fieldClass('learning_mode', 'admin-select') }}" required
                    @error('learning_mode') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('learning_mode') }}">
                @foreach ($learningModes as $mode)
                    <option value="{{ $mode->value }}" @selected($selectedMode === $mode->value)>{{ $mode->label() }}</option>
                @endforeach
            </select>
            @error('learning_mode') <p id="learning_mode-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="is_active" class="admin-label">Status Kelas <span class="is-required" aria-hidden="true">*</span></label>
            <select id="is_active" name="is_active" class="{{ $fieldClass('is_active', 'admin-select') }}" required
                    @error('is_active') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('is_active') }}">
                <option value="1" @selected($isActiveValue === '1')>Aktif</option>
                <option value="0" @selected($isActiveValue === '0')>Tidak Aktif</option>
            </select>
            @error('is_active') <p id="is_active-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field is-full">
            <label for="description" class="admin-label">Deskripsi <span class="is-optional">(opsional)</span></label>
            <textarea id="description" name="description" class="{{ $fieldClass('description', 'admin-textarea') }}" maxlength="2000" rows="3"
                      @error('description') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('description') }}">{{ old('description', $classroom?->description) }}</textarea>
            @error('description') <p id="description-error" class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
