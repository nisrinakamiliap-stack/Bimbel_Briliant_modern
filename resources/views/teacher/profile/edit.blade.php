@extends('layouts.teacher')

@section('title', 'Profil Saya')

@php
$profile = $teacher->teacherProfile;
$fieldClass = fn (string $field, string $base = 'admin-input'): string => $base.($errors->has($field) ? ' is-invalid' : '');
@endphp

@section('content')
    <div class="teacher-page-head">
        <div>
            <h1>Profil Saya</h1>
            <p>Nama, email, dan bidang ajar dikelola admin. Anda dapat memperbarui nomor telepon, alamat, dan bio.</p>
        </div>
    </div>

    <x-admin.flash />

    <div class="teacher-columns">
        <section class="admin-panel" aria-labelledby="profile-form-heading">
            <div class="admin-panel-header"><div><h2 id="profile-form-heading">Data yang dapat diubah</h2></div></div>
            <form action="{{ route('teacher.profile.update') }}" method="POST" class="admin-form" novalidate>
                @csrf
                @method('PUT')
                <div class="admin-form-grid">
                    <div class="admin-field is-full">
                        <label for="phone" class="admin-label">Nomor telepon/WhatsApp <span class="is-required" aria-hidden="true">*</span></label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $teacher->phone) }}" class="{{ $fieldClass('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="Contoh: 081234567890">
                        @error('phone') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    @if ($profile !== null)
                        <div class="admin-field is-full">
                            <label for="address" class="admin-label">Alamat <span class="is-optional">(opsional)</span></label>
                            <textarea id="address" name="address" rows="3" maxlength="500" class="{{ $fieldClass('address', 'admin-textarea') }}" autocomplete="street-address">{{ old('address', $profile->address) }}</textarea>
                            @error('address') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="admin-field is-full">
                            <label for="bio" class="admin-label">Bio singkat <span class="is-optional">(opsional)</span></label>
                            <textarea id="bio" name="bio" rows="4" maxlength="1000" class="{{ $fieldClass('bio', 'admin-textarea') }}" placeholder="Pengalaman mengajar atau pendekatan belajar Anda">{{ old('bio', $profile->bio) }}</textarea>
                            @error('bio') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <p class="admin-hint is-full">Profil guru Anda belum dilengkapi admin, jadi alamat dan bio belum dapat diisi. Hubungi admin untuk melengkapinya.</p>
                    @endif
                </div>
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary" data-loading-label="Menyimpan...">Simpan Profil</button>
                </div>
            </form>
        </section>

        <section class="admin-panel" aria-labelledby="profile-account-heading">
            <div class="admin-panel-header"><div><h2 id="profile-account-heading">Data akun</h2></div></div>
            <dl class="admin-detail-list">
                <div><dt>Nama</dt><dd>{{ $teacher->name }}</dd></div>
                <div><dt>Email</dt><dd style="overflow-wrap: anywhere;">{{ $teacher->email }}</dd></div>
                <div><dt>Peran</dt><dd>{{ $teacher->role->label() }}</dd></div>
                <div><dt>Bidang ajar</dt><dd>{{ $profile?->subject ?? 'Belum diisi admin' }}</dd></div>
                <div><dt>Pendidikan terakhir</dt><dd>{{ $profile?->last_education?->label() ?? 'Belum diisi' }}{{ $profile?->institution ? ', '.$profile->institution : '' }}</dd></div>
                <div><dt>Bergabung</dt><dd>{{ $teacher->created_at?->translatedFormat('d F Y') ?? 'Tidak tercatat' }}</dd></div>
            </dl>
        </section>
    </div>
@endsection
