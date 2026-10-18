@extends('layouts.admin')

@section('title', 'Buat Pengumuman')
@section('page_title', 'Notifikasi dan Pesan')

@section('content')
    <div class="admin-page-header">
        <div>
            <h2>Buat Pengumuman</h2>
            <p>Pengumuman dikirim sebagai notifikasi ke setiap penerima yang dipilih.</p>
        </div>
    </div>

    <x-admin.flash />
    @include('notifications.partials.tabs')

    <section class="admin-panel">
        <form action="{{ route('admin.announcements.store') }}" method="POST" class="admin-form" novalidate
              data-confirm="Kirim pengumuman ini ke penerima yang dipilih?" data-confirm-title="Kirim Pengumuman" data-confirm-button="Ya, Kirim">
            @csrf
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="audience" class="admin-label">Penerima <span class="is-required" aria-hidden="true">*</span></label>
                    <select id="audience" name="audience" class="admin-select {{ $errors->has('audience') ? 'is-invalid' : '' }}" required>
                        @foreach ($audiences as $value => $label)
                            <option value="{{ $value }}" @selected(old('audience', 'semua') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('audience') <p class="admin-error">{{ $message }}</p> @enderror
                </div>
                <div class="admin-field">
                    <label for="classroom_id" class="admin-label">Kelas <span class="is-optional">(jika penerima per kelas)</span></label>
                    <select id="classroom_id" name="classroom_id" class="admin-select {{ $errors->has('classroom_id') ? 'is-invalid' : '' }}">
                        <option value="">— Pilih kelas —</option>
                        @foreach ($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" @selected((string) old('classroom_id') === (string) $classroom->id)>{{ $classroom->name }}</option>
                        @endforeach
                    </select>
                    @error('classroom_id') <p class="admin-error">{{ $message }}</p> @enderror
                </div>
                <div class="admin-field is-full">
                    <label for="title" class="admin-label">Judul <span class="is-required" aria-hidden="true">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" class="admin-input {{ $errors->has('title') ? 'is-invalid' : '' }}" maxlength="150" required>
                    @error('title') <p class="admin-error">{{ $message }}</p> @enderror
                </div>
                <div class="admin-field is-full">
                    <label for="message" class="admin-label">Isi Pengumuman <span class="is-required" aria-hidden="true">*</span></label>
                    <textarea id="message" name="message" class="admin-textarea {{ $errors->has('message') ? 'is-invalid' : '' }}" rows="5" maxlength="2000" required>{{ old('message') }}</textarea>
                    @error('message') <p class="admin-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="admin-form-actions">
                <a href="{{ route('notifications.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">Kirim Pengumuman</button>
            </div>
        </form>
    </section>
@endsection
