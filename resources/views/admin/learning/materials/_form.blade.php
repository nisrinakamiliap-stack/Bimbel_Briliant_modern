{{--
    @var \App\Models\Material|null $material
    @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
    @var array<int, \App\LearningContentStatus> $statuses
    @var int|null $preselectedClassroomId
--}}
@php
$fieldClass = fn (string $field, string $base = 'admin-input'): string => $base.($errors->has($field) ? ' is-invalid' : '');
$selectedClassroomId = (string) old('classroom_id', $material?->classroom_id ?? $preselectedClassroomId);
$selectedStatus = old('status', $material?->status?->value ?? 'draft');
@endphp

<div class="admin-form-section">
    <h3>Informasi Materi</h3>
    <p>Materi minimal berisi teks, tautan, atau berkas.</p>
    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="classroom_id" class="admin-label">Kelas Tujuan <span class="is-required" aria-hidden="true">*</span></label>
            <select id="classroom_id" name="classroom_id" class="{{ $fieldClass('classroom_id', 'admin-select') }}" required @disabled($classrooms->isEmpty())>
                <option value="">Pilih kelas</option>
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected($selectedClassroomId === (string) $classroom->id)>{{ $classroom->name }}@if ($showTeacher ?? true) · Guru: {{ $classroom->teacher?->name ?? 'belum ditentukan' }}@endif</option>
                @endforeach
            </select>
            @error('classroom_id') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field">
            <label for="status" class="admin-label">Status <span class="is-required" aria-hidden="true">*</span></label>
            <select id="status" name="status" class="{{ $fieldClass('status', 'admin-select') }}" required>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected($selectedStatus === $statusOption->value)>{{ $statusOption->label() }}</option>
                @endforeach
            </select>
            <p class="admin-hint">Diarsipkan = disembunyikan dari murid tanpa menghapus data.</p>
            @error('status') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field is-full">
            <label for="title" class="admin-label">Judul <span class="is-required" aria-hidden="true">*</span></label>
            <input type="text" id="title" name="title" value="{{ old('title', $material?->title) }}" class="{{ $fieldClass('title') }}" maxlength="200" required>
            @error('title') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field is-full">
            <label for="description" class="admin-label">Deskripsi Singkat <span class="is-optional">(opsional)</span></label>
            <textarea id="description" name="description" class="{{ $fieldClass('description', 'admin-textarea') }}" rows="2" maxlength="1000">{{ old('description', $material?->description) }}</textarea>
            @error('description') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field is-full">
            <label for="content" class="admin-label">Isi Materi <span class="is-optional">(opsional)</span></label>
            <textarea id="content" name="content" class="{{ $fieldClass('content', 'admin-textarea') }}" rows="8">{{ old('content', $material?->content) }}</textarea>
            @error('content') <p class="admin-error" role="alert">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field is-full">
            <label for="link_url" class="admin-label">Tautan <span class="is-optional">(opsional)</span></label>
            <input type="url" id="link_url" name="link_url" value="{{ old('link_url', $material?->link_url) }}" class="{{ $fieldClass('link_url') }}" maxlength="500" placeholder="https://...">
            @error('link_url') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field is-full">
            <label for="file" class="admin-label">Berkas Pendukung <span class="is-optional">(opsional)</span></label>
            <input type="file" id="file" name="file" class="{{ $fieldClass('file') }}" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
            <p class="admin-hint">PDF, Word, PowerPoint, Excel, JPG/PNG, atau ZIP, maksimal 10 MB. Disimpan privat dan hanya dapat diunduh oleh peserta kelas.</p>
            @if ($material?->hasFile())
                <label style="display: inline-flex; gap: .4rem; align-items: center; font-size: .875rem; margin-top: .5rem;">
                    <input type="checkbox" name="remove_file" value="1"> Hapus berkas saat ini ({{ $material->file_original_name }})
                </label>
            @endif
            @error('file') <p class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
