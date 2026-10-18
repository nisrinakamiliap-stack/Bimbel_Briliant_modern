{{--
    Field form program yang dipakai bersama oleh halaman tambah dan edit.

    @var \App\Models\Program|null $program
    @var array<int, \App\ProgramLevel> $levels
--}}
@php
$fieldClass = fn (string $field, string $base = 'admin-input'): string => $base.($errors->has($field) ? ' is-invalid' : '');
$describedBy = fn (string $field, bool $hasHint = false): string => collect([
    $hasHint ? $field.'-hint' : null,
    $errors->has($field) ? $field.'-error' : null,
])->filter()->implode(' ');

$priceValue = old('price', $program !== null ? rtrim(rtrim((string) $program->price, '0'), '.') : '');
$isActiveValue = (string) old('is_active', $program === null || $program->is_active ? '1' : '0');
@endphp

<div class="admin-form-section">
    <h3>Informasi Program</h3>
    <p>Nama, jenjang, dan deskripsi program yang ditawarkan.</p>

    <div class="admin-form-grid">
        <div class="admin-field is-full">
            <label for="name" class="admin-label">Nama Program <span class="is-required" aria-hidden="true">*</span></label>
            <input type="text" id="name" name="name" class="{{ $fieldClass('name') }}"
                   value="{{ old('name', $program?->name) }}" maxlength="150" required placeholder="Contoh: Bimbingan Belajar SMA"
                   @error('name') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('name') }}">
            @error('name') <p id="name-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="level" class="admin-label">Jenjang Pendidikan <span class="is-required" aria-hidden="true">*</span></label>
            <select id="level" name="level" class="{{ $fieldClass('level', 'admin-select') }}" required
                    @error('level') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('level') }}">
                <option value="">— Pilih jenjang —</option>
                @foreach ($levels as $levelOption)
                    <option value="{{ $levelOption->value }}" @selected(old('level', $program?->level?->value) === $levelOption->value)>{{ $levelOption->label() }}</option>
                @endforeach
            </select>
            @error('level') <p id="level-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="is_active" class="admin-label">Status Program <span class="is-required" aria-hidden="true">*</span></label>
            <select id="is_active" name="is_active" class="{{ $fieldClass('is_active', 'admin-select') }}" required
                    @error('is_active') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('is_active', true) }}">
                <option value="1" @selected($isActiveValue === '1')>Aktif</option>
                <option value="0" @selected($isActiveValue === '0')>Tidak Aktif</option>
            </select>
            <p id="is_active-hint" class="admin-hint">Program tidak aktif tidak lagi ditawarkan, tetapi datanya tetap tersimpan.</p>
            @error('is_active') <p id="is_active-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field is-full">
            <label for="description" class="admin-label">Deskripsi <span class="is-optional">(opsional)</span></label>
            <textarea id="description" name="description" class="{{ $fieldClass('description', 'admin-textarea') }}" maxlength="2000" rows="4"
                      @error('description') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('description') }}">{{ old('description', $program?->description) }}</textarea>
            @error('description') <p id="description-error" class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3>Harga &amp; Durasi</h3>
    <p>Biaya program dan lama program berlangsung.</p>

    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="price" class="admin-label">Harga (Rp) <span class="is-required" aria-hidden="true">*</span></label>
            <input type="text" id="price" name="price" class="{{ $fieldClass('price') }}" inputmode="decimal"
                   value="{{ $priceValue }}" placeholder="Contoh: 350000" required
                   @error('price') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('price', true) }}">
            <p id="price-hint" class="admin-hint">Isi angka tanpa titik atau dengan format 350.000. Tidak boleh negatif.</p>
            @error('price') <p id="price-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="duration_months" class="admin-label">Durasi (bulan) <span class="is-optional">(opsional)</span></label>
            <input type="number" id="duration_months" name="duration_months" class="{{ $fieldClass('duration_months') }}"
                   value="{{ old('duration_months', $program?->duration_months) }}" min="1" max="60" step="1" placeholder="Contoh: 6"
                   @error('duration_months') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('duration_months', true) }}">
            <p id="duration_months-hint" class="admin-hint">Kosongkan jika program berjalan fleksibel per bulan.</p>
            @error('duration_months') <p id="duration_months-error" class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
