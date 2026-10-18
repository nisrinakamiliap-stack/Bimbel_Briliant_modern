@extends('layouts.admin')

@section('title', 'Pengaturan')
@section('page_title', 'Pengaturan')

@php
$field = fn (string $key) => old($key, $values[$key] ?? match ($key) {
    'institution_name' => $institution['name'],
    'institution_address' => $institution['address'],
    'institution_phone' => $institution['phone'],
    'institution_whatsapp' => $institution['whatsapp'],
    'institution_email' => $institution['email'],
    'institution_hours' => $institution['hours'],
    default => null,
});
$invalid = fn (string $key) => $errors->has($key) ? ' is-invalid' : '';
@endphp

@section('content')
    <div class="admin-page-header">
        <div>
            <h2>Pengaturan</h2>
            <p>Profil lembaga dan pengaturan dasar aplikasi. Konfigurasi server dan rahasia aplikasi tidak dapat diubah dari halaman ini.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf
            @method('PUT')

            <div class="admin-form-section">
                <h3>Profil Lembaga</h3>
                <p>Ditampilkan pada panel admin dan portal pengguna.</p>
                <div class="admin-form-grid">
                    <div class="admin-field">
                        <label for="institution_name" class="admin-label">Nama Lembaga <span class="is-required" aria-hidden="true">*</span></label>
                        <input type="text" id="institution_name" name="institution_name" value="{{ $field('institution_name') }}" class="admin-input{{ $invalid('institution_name') }}" maxlength="120" required>
                        @error('institution_name') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="admin-field">
                        <label for="institution_tagline" class="admin-label">Slogan <span class="is-optional">(opsional)</span></label>
                        <input type="text" id="institution_tagline" name="institution_tagline" value="{{ $field('institution_tagline') }}" class="admin-input{{ $invalid('institution_tagline') }}" maxlength="160">
                        @error('institution_tagline') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="admin-field is-full">
                        <label for="institution_description" class="admin-label">Deskripsi Singkat <span class="is-optional">(opsional)</span></label>
                        <textarea id="institution_description" name="institution_description" class="admin-textarea{{ $invalid('institution_description') }}" rows="3" maxlength="1000">{{ $field('institution_description') }}</textarea>
                        @error('institution_description') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="admin-field is-full">
                        <label for="institution_address" class="admin-label">Alamat</label>
                        <textarea id="institution_address" name="institution_address" class="admin-textarea{{ $invalid('institution_address') }}" rows="2" maxlength="300">{{ $field('institution_address') }}</textarea>
                        @error('institution_address') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="admin-field">
                        <label for="institution_phone" class="admin-label">Telepon</label>
                        <input type="text" id="institution_phone" name="institution_phone" value="{{ $field('institution_phone') }}" class="admin-input{{ $invalid('institution_phone') }}" maxlength="40">
                        @error('institution_phone') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="admin-field">
                        <label for="institution_whatsapp" class="admin-label">WhatsApp</label>
                        <input type="text" id="institution_whatsapp" name="institution_whatsapp" value="{{ $field('institution_whatsapp') }}" class="admin-input{{ $invalid('institution_whatsapp') }}" maxlength="40">
                        @error('institution_whatsapp') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="admin-field">
                        <label for="institution_email" class="admin-label">Email</label>
                        <input type="email" id="institution_email" name="institution_email" value="{{ $field('institution_email') }}" class="admin-input{{ $invalid('institution_email') }}" maxlength="150">
                        @error('institution_email') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="admin-field">
                        <label for="institution_hours" class="admin-label">Jam Operasional</label>
                        <input type="text" id="institution_hours" name="institution_hours" value="{{ $field('institution_hours') }}" class="admin-input{{ $invalid('institution_hours') }}" maxlength="120">
                        @error('institution_hours') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="admin-field is-full">
                        <label for="logo" class="admin-label">Logo <span class="is-optional">(PNG/JPG/WebP, maks. 1 MB)</span></label>
                        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                            <img src="{{ $institution['logo_url'] }}" alt="Logo saat ini" style="height: 3rem; width: auto; border: 1px solid var(--admin-border); border-radius: .5rem; padding: .25rem; background: #fff;">
                            <input type="file" id="logo" name="logo" class="admin-input{{ $invalid('logo') }}" accept=".png,.jpg,.jpeg,.webp" style="flex: 1; min-width: 14rem;">
                        </div>
                        @if (! empty($values['institution_logo_path']))
                            <label style="display: inline-flex; gap: .4rem; align-items: center; font-size: .875rem; margin-top: .5rem;"><input type="checkbox" name="remove_logo" value="1"> Kembalikan ke logo bawaan</label>
                        @endif
                        @error('logo') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="admin-form-section">
                <h3>Pengaturan Aplikasi</h3>
                <p>Zona waktu dipakai untuk jadwal, check-in guru, tenggat tugas, dan seluruh catatan waktu. Saat ini: {{ $currentTimezone }}.</p>
                <div class="admin-form-grid">
                    <div class="admin-field">
                        <label for="app_timezone" class="admin-label">Zona Waktu <span class="is-required" aria-hidden="true">*</span></label>
                        <select id="app_timezone" name="app_timezone" class="admin-select{{ $invalid('app_timezone') }}" required>
                            @foreach ($timezones as $value => $label)
                                <option value="{{ $value }}" @selected(old('app_timezone', $values['app_timezone'] ?? $currentTimezone) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('app_timezone') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary"><x-admin.icon name="check" :size="16" /> Simpan Pengaturan</button>
            </div>
        </form>
    </section>
@endsection
