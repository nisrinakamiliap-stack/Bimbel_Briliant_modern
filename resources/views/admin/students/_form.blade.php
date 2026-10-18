{{--
    Field form murid yang dipakai bersama oleh halaman tambah dan edit.

    @var \App\Models\User|null $student
    @var array<int, \App\EducationLevel> $educationLevels
--}}
@php
$profile = $student?->studentProfile;
$isCreating = $student === null;

$fieldClass = fn (string $field, string $base = 'admin-input'): string => $base.($errors->has($field) ? ' is-invalid' : '');
$describedBy = fn (string $field, bool $hasHint = false): string => collect([
    $hasHint ? $field.'-hint' : null,
    $errors->has($field) ? $field.'-error' : null,
])->filter()->implode(' ');
@endphp

<div class="admin-form-section">
    <h3>Data Akun</h3>
    <p>Digunakan murid untuk login ke portal belajar.</p>

    <div class="admin-form-grid">
        <div class="admin-field is-full">
            <label for="name" class="admin-label">Nama Lengkap <span class="is-required" aria-hidden="true">*</span></label>
            <input type="text" id="name" name="name" class="{{ $fieldClass('name') }}"
                   value="{{ old('name', $student?->name) }}" maxlength="100" required autocomplete="off"
                   @error('name') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('name') }}">
            @error('name') <p id="name-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="email" class="admin-label">Email <span class="is-required" aria-hidden="true">*</span></label>
            <input type="email" id="email" name="email" class="{{ $fieldClass('email') }}"
                   value="{{ old('email', $student?->email) }}" maxlength="255" required autocomplete="off"
                   @error('email') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('email') }}">
            @error('email') <p id="email-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="phone" class="admin-label">No. Telepon / WhatsApp <span class="is-required" aria-hidden="true">*</span></label>
            <input type="tel" id="phone" name="phone" class="{{ $fieldClass('phone') }}" inputmode="tel"
                   value="{{ old('phone', $student?->phone) }}" placeholder="081234567890" required
                   @error('phone') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('phone') }}">
            @error('phone') <p id="phone-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        @if ($isCreating)
            <div class="admin-field">
                <label for="password" class="admin-label">Password Awal <span class="is-required" aria-hidden="true">*</span></label>
                <input type="password" id="password" name="password" class="{{ $fieldClass('password') }}"
                       autocomplete="new-password" required
                       @error('password') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('password', true) }}">
                <p id="password-hint" class="admin-hint">Minimal 8 karakter, berisi huruf dan angka. Sampaikan ke murid secara pribadi.</p>
                @error('password') <p id="password-error" class="admin-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label for="password_confirmation" class="admin-label">Konfirmasi Password <span class="is-required" aria-hidden="true">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="admin-input"
                       autocomplete="new-password" required>
            </div>
        @endif
    </div>
</div>

<div class="admin-form-section">
    <h3>Profil Murid</h3>
    <p>Informasi pendidikan dan kontak orang tua/wali.</p>

    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="education_level" class="admin-label">Jenjang Pendidikan <span class="is-required" aria-hidden="true">*</span></label>
            <select id="education_level" name="education_level" class="{{ $fieldClass('education_level', 'admin-select') }}" required
                    @error('education_level') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('education_level') }}">
                <option value="">— Pilih jenjang —</option>
                @foreach ($educationLevels as $level)
                    <option value="{{ $level->value }}" @selected(old('education_level', $profile?->education_level?->value) === $level->value)>{{ $level->label() }}</option>
                @endforeach
            </select>
            @error('education_level') <p id="education_level-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="school_name" class="admin-label">Asal Sekolah <span class="is-optional">(opsional)</span></label>
            <input type="text" id="school_name" name="school_name" class="{{ $fieldClass('school_name') }}"
                   value="{{ old('school_name', $profile?->school_name) }}" maxlength="150"
                   @error('school_name') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('school_name') }}">
            @error('school_name') <p id="school_name-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="birth_date" class="admin-label">Tanggal Lahir <span class="is-optional">(opsional)</span></label>
            <input type="date" id="birth_date" name="birth_date" class="{{ $fieldClass('birth_date') }}"
                   value="{{ old('birth_date', $profile?->birth_date?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}"
                   @error('birth_date') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('birth_date') }}">
            @error('birth_date') <p id="birth_date-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field is-full">
            <label for="address" class="admin-label">Alamat <span class="is-optional">(opsional)</span></label>
            <textarea id="address" name="address" class="{{ $fieldClass('address', 'admin-textarea') }}" maxlength="500" rows="3"
                      @error('address') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('address') }}">{{ old('address', $profile?->address) }}</textarea>
            @error('address') <p id="address-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="parent_name" class="admin-label">Nama Orang Tua / Wali <span class="is-optional">(opsional)</span></label>
            <input type="text" id="parent_name" name="parent_name" class="{{ $fieldClass('parent_name') }}"
                   value="{{ old('parent_name', $profile?->parent_name) }}" maxlength="100"
                   @error('parent_name') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('parent_name') }}">
            @error('parent_name') <p id="parent_name-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="parent_phone" class="admin-label">Telepon Orang Tua / Wali <span class="is-optional">(opsional)</span></label>
            <input type="tel" id="parent_phone" name="parent_phone" class="{{ $fieldClass('parent_phone') }}" inputmode="tel"
                   value="{{ old('parent_phone', $profile?->parent_phone) }}" placeholder="081234567890"
                   @error('parent_phone') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('parent_phone') }}">
            @error('parent_phone') <p id="parent_phone-error" class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
