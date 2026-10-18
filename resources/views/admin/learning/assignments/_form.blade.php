{{--
    @var \App\Models\Assignment|null $assignment
    @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
    @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Material> $materials
    @var array<int, \App\LearningContentStatus> $statuses
    @var int|null $preselectedClassroomId
--}}
@php
$fieldClass = fn (string $field, string $base = 'admin-input'): string => $base.($errors->has($field) ? ' is-invalid' : '');
$selectedClassroomId = (string) old('classroom_id', $assignment?->classroom_id ?? $preselectedClassroomId);
$selectedMaterialId = (string) old('material_id', $assignment?->material_id);
$selectedStatus = old('status', $assignment?->status?->value ?? 'draft');
$dueAtValue = old('due_at', $assignment?->due_at?->format('Y-m-d\TH:i'));
@endphp

<div class="admin-form-section">
    <h3>Informasi Tugas</h3>
    <p>Tugas berstatus Terbit dapat dilihat dan dikumpulkan oleh murid peserta kelas.</p>
    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="classroom_id" class="admin-label">Kelas Tujuan <span class="is-required" aria-hidden="true">*</span></label>
            <select id="classroom_id" name="classroom_id" class="{{ $fieldClass('classroom_id', 'admin-select') }}" required @disabled($classrooms->isEmpty())>
                <option value="">— Pilih kelas —</option>
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected($selectedClassroomId === (string) $classroom->id)>{{ $classroom->name }} — Guru: {{ $classroom->teacher?->name ?? 'belum ditentukan' }}</option>
                @endforeach
            </select>
            @error('classroom_id') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field">
            <label for="material_id" class="admin-label">Materi Terkait <span class="is-optional">(opsional)</span></label>
            <select id="material_id" name="material_id" class="{{ $fieldClass('material_id', 'admin-select') }}">
                <option value="">— Tanpa materi —</option>
                @foreach ($materials as $materialOption)
                    <option value="{{ $materialOption->id }}" data-classroom-id="{{ $materialOption->classroom_id }}" @selected($selectedMaterialId === (string) $materialOption->id)>{{ $materialOption->title }}</option>
                @endforeach
            </select>
            <p class="admin-hint">Hanya materi dari kelas yang sama.</p>
            @error('material_id') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field is-full">
            <label for="title" class="admin-label">Judul Tugas <span class="is-required" aria-hidden="true">*</span></label>
            <input type="text" id="title" name="title" value="{{ old('title', $assignment?->title) }}" class="{{ $fieldClass('title') }}" maxlength="200" required>
            @error('title') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field is-full">
            <label for="instructions" class="admin-label">Instruksi <span class="is-required" aria-hidden="true">*</span></label>
            <textarea id="instructions" name="instructions" class="{{ $fieldClass('instructions', 'admin-textarea') }}" rows="6" required>{{ old('instructions', $assignment?->instructions) }}</textarea>
            @error('instructions') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3>Tenggat &amp; Penilaian</h3>
    <p>Waktu mengikuti zona waktu aplikasi ({{ config('app.timezone') }}).</p>
    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="due_at" class="admin-label">Tenggat <span class="is-required" aria-hidden="true">*</span></label>
            <input type="datetime-local" id="due_at" name="due_at" value="{{ $dueAtValue }}" class="{{ $fieldClass('due_at') }}" required>
            @error('due_at') <p class="admin-error" role="alert">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field">
            <label for="max_score" class="admin-label">Skala Nilai Maksimal <span class="is-required" aria-hidden="true">*</span></label>
            <input type="number" id="max_score" name="max_score" value="{{ old('max_score', $assignment?->max_score ?? 100) }}" class="{{ $fieldClass('max_score') }}" min="1" max="1000" required>
            <p class="admin-hint">Nilai yang diberikan berada pada rentang 0 sampai angka ini.</p>
            @error('max_score') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field">
            <label class="admin-label" style="display: flex; gap: .5rem; align-items: center;">
                <input type="checkbox" name="allow_late_submission" value="1" @checked(old('allow_late_submission', $assignment?->allow_late_submission))>
                Terima pengumpulan setelah tenggat (ditandai terlambat)
            </label>
            <label class="admin-label" style="display: flex; gap: .5rem; align-items: center;">
                <input type="checkbox" name="allow_resubmission" value="1" @checked(old('allow_resubmission', $assignment?->allow_resubmission ?? true))>
                Murid boleh memperbarui pengumpulan sebelum dinilai
            </label>
        </div>
        <div class="admin-field">
            <label for="status" class="admin-label">Status <span class="is-required" aria-hidden="true">*</span></label>
            <select id="status" name="status" class="{{ $fieldClass('status', 'admin-select') }}" required>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected($selectedStatus === $statusOption->value)>{{ $statusOption->label() }}</option>
                @endforeach
            </select>
            @error('status') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field is-full">
            <label for="attachment" class="admin-label">Lampiran <span class="is-optional">(opsional)</span></label>
            <input type="file" id="attachment" name="attachment" class="{{ $fieldClass('attachment') }}" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
            @if ($assignment?->hasAttachment())
                <label style="display: inline-flex; gap: .4rem; align-items: center; font-size: .875rem; margin-top: .5rem;">
                    <input type="checkbox" name="remove_attachment" value="1"> Hapus lampiran saat ini ({{ $assignment->attachment_original_name }})
                </label>
            @endif
            @error('attachment') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';
    var classroomSelect = document.getElementById('classroom_id');
    var materialSelect = document.getElementById('material_id');
    if (!classroomSelect || !materialSelect) return;
    function filterMaterials() {
        materialSelect.querySelectorAll('option[data-classroom-id]').forEach(function (option) {
            var matches = option.getAttribute('data-classroom-id') === classroomSelect.value;
            option.hidden = !matches;
            option.disabled = !matches;
            if (!matches && option.selected) materialSelect.value = '';
        });
    }
    classroomSelect.addEventListener('change', filterMaterials);
    filterMaterials();
}());
</script>
@endpush
