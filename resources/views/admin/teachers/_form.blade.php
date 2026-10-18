{{--
    Field form guru yang dipakai bersama oleh halaman tambah dan edit.

    @var \App\Models\User|null $teacher
    @var array<int, \App\EducationDegree> $educationDegrees
    @var \Illuminate\Support\Collection<int, string> $subjects
--}}
@php
$profile = $teacher?->teacherProfile;
$isCreating = $teacher === null;

$fieldClass = fn (string $field, string $base = 'admin-input'): string => $base.($errors->has($field) ? ' is-invalid' : '');
$describedBy = fn (string $field, bool $hasHint = false): string => collect([
    $hasHint ? $field.'-hint' : null,
    $errors->has($field) ? $field.'-error' : null,
])->filter()->implode(' ');
@endphp

<div class="admin-form-section">
    <h3>Data Akun</h3>
    <p>Digunakan guru untuk login ke portal pengajar.</p>

    <div class="admin-form-grid">
        <div class="admin-field is-full">
            <label for="name" class="admin-label">Nama Lengkap <span class="is-required" aria-hidden="true">*</span></label>
            <input type="text" id="name" name="name" class="{{ $fieldClass('name') }}"
                   value="{{ old('name', $teacher?->name) }}" maxlength="100" required autocomplete="off"
                   @error('name') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('name') }}">
            @error('name') <p id="name-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="email" class="admin-label">Email <span class="is-required" aria-hidden="true">*</span></label>
            <input type="email" id="email" name="email" class="{{ $fieldClass('email') }}"
                   value="{{ old('email', $teacher?->email) }}" maxlength="255" required autocomplete="off"
                   @error('email') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('email') }}">
            @error('email') <p id="email-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="phone" class="admin-label">No. Telepon / WhatsApp <span class="is-required" aria-hidden="true">*</span></label>
            <input type="tel" id="phone" name="phone" class="{{ $fieldClass('phone') }}" inputmode="tel"
                   value="{{ old('phone', $teacher?->phone) }}" placeholder="081234567890" required
                   @error('phone') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('phone') }}">
            @error('phone') <p id="phone-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        @if ($isCreating)
            <div class="admin-field">
                <label for="password" class="admin-label">Password Awal <span class="is-required" aria-hidden="true">*</span></label>
                <input type="password" id="password" name="password" class="{{ $fieldClass('password') }}"
                       autocomplete="new-password" required
                       @error('password') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('password', true) }}">
                <p id="password-hint" class="admin-hint">Minimal 8 karakter, berisi huruf dan angka. Sampaikan ke guru secara pribadi.</p>
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
    <h3>Profil Pengajar</h3>
    <p>Bidang keahlian dan latar belakang profesional guru.</p>

    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="subject" class="admin-label">Bidang Keahlian / Mata Pelajaran <span class="is-required" aria-hidden="true">*</span></label>
            <input type="text" id="subject" name="subject" class="{{ $fieldClass('subject') }}" list="subject-suggestions"
                   value="{{ old('subject', $profile?->subject) }}" maxlength="100" placeholder="Contoh: Matematika" required
                   @error('subject') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('subject', true) }}">
            <datalist id="subject-suggestions">
                @foreach ($subjects as $existingSubject)
                    <option value="{{ $existingSubject }}"></option>
                @endforeach
            </datalist>
            <p id="subject-hint" class="admin-hint">Gunakan penulisan yang sama dengan guru lain agar filter berfungsi.</p>
            @error('subject') <p id="subject-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field">
            <label for="last_education" class="admin-label">Pendidikan Terakhir <span class="is-optional">(opsional)</span></label>
            <select id="last_education" name="last_education" class="{{ $fieldClass('last_education', 'admin-select') }}"
                    @error('last_education') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('last_education') }}">
                <option value="">— Pilih pendidikan —</option>
                @foreach ($educationDegrees as $degree)
                    <option value="{{ $degree->value }}" @selected(old('last_education', $profile?->last_education?->value) === $degree->value)>{{ $degree->label() }}</option>
                @endforeach
            </select>
            @error('last_education') <p id="last_education-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field is-full">
            <label for="institution" class="admin-label">Institusi / Kampus Asal <span class="is-optional">(opsional)</span></label>
            <input type="text" id="institution" name="institution" class="{{ $fieldClass('institution') }}"
                   value="{{ old('institution', $profile?->institution) }}" maxlength="150"
                   @error('institution') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('institution') }}">
            @error('institution') <p id="institution-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field is-full">
            <label for="address" class="admin-label">Alamat <span class="is-optional">(opsional)</span></label>
            <textarea id="address" name="address" class="{{ $fieldClass('address', 'admin-textarea') }}" maxlength="500" rows="3"
                      @error('address') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('address') }}">{{ old('address', $profile?->address) }}</textarea>
            @error('address') <p id="address-error" class="admin-error">{{ $message }}</p> @enderror
        </div>

        <div class="admin-field is-full">
            <label for="bio" class="admin-label">Profil Singkat / Pengalaman Mengajar <span class="is-optional">(opsional)</span></label>
            <textarea id="bio" name="bio" class="{{ $fieldClass('bio', 'admin-textarea') }}" maxlength="1000" rows="4"
                      @error('bio') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('bio') }}">{{ old('bio', $profile?->bio) }}</textarea>
            @error('bio') <p id="bio-error" class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
