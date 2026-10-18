@extends('layouts.student')

@section('title', 'Profil Saya — Portal Murid')

@section('content')
<div class="st-container st-page">
    <div class="st-page-header">
        <h1 class="st-page-title">Profil Saya</h1>
        <p class="st-page-sub">Perbarui informasi pribadi dan keamanan akun Anda.</p>
    </div>

    @if (session('success'))
        <div class="st-flash st-flash--success" role="status">{{ session('success') }}</div>
    @endif

    {{-- Avatar & nama --}}
    <div class="st-profile-header" style="margin-bottom:2rem;">
        <div class="st-profile-avatar-lg" aria-hidden="true">
            {{ strtoupper(mb_substr($student->name, 0, 1)) }}
        </div>
        <div>
            <h2 style="margin:0;font-size:1.25rem;font-weight:800;color:var(--st-forest);">{{ $student->name }}</h2>
            <p style="margin:.25rem 0 0;font-size:.875rem;color:var(--st-muted);">{{ $student->email }}</p>
            @if ($profile?->education_level)
                <span class="st-status is-info" style="margin-top:.5rem;display:inline-flex;">{{ $profile->education_level->label() }}</span>
            @endif
        </div>
    </div>

    <div class="st-grid-2" style="align-items:flex-start;gap:1.5rem;">
        {{-- Form informasi pribadi --}}
        <div>
            <div class="st-detail-card">
                <div class="st-detail-header">
                    <h2 class="st-detail-title" style="font-size:1.0625rem;">Informasi Pribadi</h2>
                </div>
                <div class="st-detail-body">
                    <form action="{{ route('student.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="st-form-group">
                            <label class="st-label">Nama Lengkap</label>
                            <input type="text" class="st-input" value="{{ $student->name }}" disabled>
                            <p style="font-size:.75rem;color:var(--st-muted);margin:.25rem 0 0;">Nama tidak dapat diubah sendiri. Hubungi admin jika perlu.</p>
                        </div>

                        <div class="st-form-group">
                            <label class="st-label">Email</label>
                            <input type="email" class="st-input" value="{{ $student->email }}" disabled>
                        </div>

                        <div class="st-form-group">
                            <label for="phone" class="st-label">Nomor Telepon</label>
                            <input type="tel" id="phone" name="phone" class="st-input @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $student->phone) }}"
                                   placeholder="Contoh: 081234567890"
                                   autocomplete="tel">
                            @error('phone')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="address" class="st-label">Alamat</label>
                            <textarea id="address" name="address" class="st-textarea @error('address') is-invalid @enderror"
                                      rows="3" placeholder="Alamat lengkap">{{ old('address', $profile?->address) }}</textarea>
                            @error('address')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="birth_date" class="st-label">Tanggal Lahir</label>
                            <input type="date" id="birth_date" name="birth_date" class="st-input @error('birth_date') is-invalid @enderror"
                                   value="{{ old('birth_date', $profile?->birth_date?->format('Y-m-d')) }}"
                                   max="{{ now()->subDay()->format('Y-m-d') }}">
                            @error('birth_date')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="education_level" class="st-label">Jenjang Pendidikan</label>
                            <select id="education_level" name="education_level" class="st-select @error('education_level') is-invalid @enderror">
                                <option value="">Pilih jenjang</option>
                                @foreach ($educationLevels as $level)
                                    <option value="{{ $level->value }}" @selected(old('education_level', $profile?->education_level?->value) === $level->value)>
                                        {{ $level->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('education_level')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="school_name" class="st-label">Nama Sekolah</label>
                            <input type="text" id="school_name" name="school_name" class="st-input @error('school_name') is-invalid @enderror"
                                   value="{{ old('school_name', $profile?->school_name) }}"
                                   placeholder="Contoh: SMP Negeri 1 Kota">
                            @error('school_name')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <hr style="border:none;border-top:1px solid var(--st-border);margin:1.25rem 0;">

                        <h3 style="font-size:.9375rem;font-weight:700;color:var(--st-muted);margin:0 0 1rem;text-transform:uppercase;letter-spacing:.04em;">Data Orang Tua / Wali</h3>

                        <div class="st-form-group">
                            <label for="parent_name" class="st-label">Nama Orang Tua / Wali</label>
                            <input type="text" id="parent_name" name="parent_name" class="st-input @error('parent_name') is-invalid @enderror"
                                   value="{{ old('parent_name', $profile?->parent_name) }}"
                                   placeholder="Nama lengkap orang tua atau wali">
                            @error('parent_name')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="parent_phone" class="st-label">No. Telepon Orang Tua / Wali</label>
                            <input type="tel" id="parent_phone" name="parent_phone" class="st-input @error('parent_phone') is-invalid @enderror"
                                   value="{{ old('parent_phone', $profile?->parent_phone) }}"
                                   placeholder="Contoh: 081234567890">
                            @error('parent_phone')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="st-btn st-btn-primary st-btn-full">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Form ganti password --}}
        <div>
            <div class="st-detail-card">
                <div class="st-detail-header">
                    <h2 class="st-detail-title" style="font-size:1.0625rem;">Ganti Password</h2>
                    <p style="margin:.25rem 0 0;font-size:.875rem;color:var(--st-muted);">Gunakan password yang kuat dan unik.</p>
                </div>
                <div class="st-detail-body">
                    <form action="{{ route('student.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="st-form-group">
                            <label for="current_password" class="st-label">Password Saat Ini <span aria-hidden="true" style="color:#dc2626;">*</span></label>
                            <input type="password" id="current_password" name="current_password"
                                   class="st-input @error('current_password') is-invalid @enderror"
                                   autocomplete="current-password" required>
                            @error('current_password')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="password" class="st-label">Password Baru <span aria-hidden="true" style="color:#dc2626;">*</span></label>
                            <input type="password" id="password" name="password"
                                   class="st-input @error('password') is-invalid @enderror"
                                   autocomplete="new-password" required>
                            @error('password')<p class="st-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="st-form-group">
                            <label for="password_confirmation" class="st-label">Konfirmasi Password Baru <span aria-hidden="true" style="color:#dc2626;">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="st-input"
                                   autocomplete="new-password" required>
                        </div>

                        <button type="submit" class="st-btn st-btn-secondary st-btn-full">
                            Perbarui Password
                        </button>
                    </form>
                </div>
            </div>

            {{-- Info akun --}}
            <div class="st-card" style="margin-top:1rem;">
                <h3 style="margin:0 0 .75rem;font-size:.875rem;font-weight:700;color:var(--st-muted);text-transform:uppercase;letter-spacing:.04em;">Info Akun</h3>
                <div style="display:flex;flex-direction:column;gap:.5rem;font-size:.875rem;">
                    <div style="display:flex;justify-content:space-between;gap:.5rem;">
                        <span style="color:var(--st-muted);">Bergabung sejak</span>
                        <span style="font-weight:600;">{{ $student->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
