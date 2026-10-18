@extends($layout)

@section('title', 'Pesan Baru')
@section('page_title', 'Notifikasi dan Pesan')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('messages.index') }}">Pesan</a></li>
                <li aria-current="page">Pesan Baru</li>
            </ol>
            <h2>Pesan Baru</h2>
            <p>Penerima dibatasi sesuai hak akses: admin, serta guru dan murid pada kelas yang sama.</p>
        </div>
    </div>

    <x-admin.flash />

    <section class="admin-panel">
        @if ($contacts->isEmpty())
            <x-admin.empty-state title="Belum ada penerima yang dapat dihubungi" icon="inbox">Penerima tersedia setelah Anda terdaftar di kelas atau ditetapkan sebagai pengajar.</x-admin.empty-state>
        @else
            <form action="{{ route('messages.store') }}" method="POST" class="admin-form" novalidate>
                @csrf
                <div class="admin-form-grid" style="grid-template-columns: 1fr;">
                    <div class="admin-field">
                        <label for="recipient_id" class="admin-label">Penerima <span class="is-required" aria-hidden="true">*</span></label>
                        <select id="recipient_id" name="recipient_id" class="admin-select {{ $errors->has('recipient_id') ? 'is-invalid' : '' }}" required>
                            <option value="">— Pilih penerima —</option>
                            @foreach ($contacts as $contact)
                                <option value="{{ $contact->id }}" @selected((string) old('recipient_id', $preselectedRecipientId) === (string) $contact->id)>{{ $contact->name }} — {{ $contact->role->label() }} ({{ $contact->email }})</option>
                            @endforeach
                        </select>
                        @error('recipient_id') <p class="admin-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="admin-field">
                        <label for="body" class="admin-label">Pesan <span class="is-required" aria-hidden="true">*</span></label>
                        <textarea id="body" name="body" class="admin-textarea {{ $errors->has('body') ? 'is-invalid' : '' }}" rows="5" maxlength="2000" required>{{ old('body') }}</textarea>
                        @error('body') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="admin-form-actions">
                    <a href="{{ route('messages.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                    <button type="submit" class="admin-btn admin-btn-primary">Kirim Pesan</button>
                </div>
            </form>
        @endif
    </section>
@endsection
