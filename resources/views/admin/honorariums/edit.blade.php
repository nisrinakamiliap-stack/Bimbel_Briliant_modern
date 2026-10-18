@extends('layouts.admin')

@section('title', 'Edit Honorarium')
@section('page_title', 'Honorarium Guru')

@use('App\Models\TeacherHonorarium')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.honorariums.index') }}">Honorarium</a></li>
                <li><a href="{{ route('admin.honorariums.show', $honorarium) }}">{{ $honorarium->teacher->name }} ({{ $honorarium->periodLabel() }})</a></li>
                <li aria-current="page">Edit</li>
            </ol>
            <h2>Edit Penetapan Honorarium</h2>
        </div>
        <a href="{{ route('admin.honorariums.show', $honorarium) }}" class="admin-btn admin-btn-secondary">
            <x-admin.icon name="arrow-left" :size="16" />
            Kembali
        </a>
    </div>

    <x-admin.flash />

    <div class="admin-grid">
        <form method="POST" action="{{ route('admin.honorariums.update', $honorarium) }}" class="admin-panel is-wide" novalidate>
            @csrf
            @method('PUT')

            <div class="admin-panel-header">
                <div>
                    <h3>Edit Data Honorarium (Draft)</h3>
                    <p>{{ $honorarium->teacher->name }} ({{ $honorarium->periodLabel() }})</p>
                </div>
            </div>

            <div class="admin-form-body">
                <p class="admin-hint" style="margin-bottom: 1rem;">
                    Guru dan periode tidak dapat diubah setelah dibuat. Untuk periode lain, buat catatan baru.
                </p>

                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="session_count" class="admin-label">Jumlah Sesi Mengajar <span class="admin-required">*</span></label>
                        <input type="number" id="session_count" name="session_count"
                            class="admin-input @error('session_count') is-error @enderror"
                            value="{{ old('session_count', $honorarium->session_count) }}"
                            min="0" max="9999" required>
                        @error('session_count')
                            <p class="admin-hint is-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="admin-form-group">
                        <label for="rate_per_session" class="admin-label">Tarif per Sesi (Rp) <span class="admin-required">*</span></label>
                        <input type="text" id="rate_per_session" name="rate_per_session"
                            class="admin-input @error('rate_per_session') is-error @enderror"
                            value="{{ old('rate_per_session', $honorarium->rate_per_session) }}"
                            required>
                        @error('rate_per_session')
                            <p class="admin-hint is-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="adjustments" class="admin-label">Penyesuaian / Tunjangan / Bonus (Rp)</label>
                        <input type="text" id="adjustments" name="adjustments"
                            class="admin-input @error('adjustments') is-error @enderror"
                            value="{{ old('adjustments', $honorarium->adjustments) }}">
                        @error('adjustments')
                            <p class="admin-hint is-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="admin-form-group">
                        <label for="deductions" class="admin-label">Potongan yang Disetujui (Rp)</label>
                        <input type="text" id="deductions" name="deductions"
                            class="admin-input @error('deductions') is-error @enderror"
                            value="{{ old('deductions', $honorarium->deductions) }}">
                        @error('deductions')
                            <p class="admin-hint is-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="notes" class="admin-label">Catatan</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="admin-input @error('notes') is-error @enderror">{{ old('notes', $honorarium->notes) }}</textarea>
                    @error('notes')
                        <p class="admin-hint is-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.honorariums.show', $honorarium) }}" class="admin-btn admin-btn-ghost">Batal</a>
            </div>
        </form>
    </div>
@endsection
