@extends('layouts.admin')

@section('title', 'Catat Pembayaran')
@section('page_title', 'Pembayaran')

@use('App\Http\Requests\Admin\StorePaymentRequest')
@use('App\Models\Payment')

@php
$fieldClass = fn (string $field, string $base = 'admin-input'): string => $base.($errors->has($field) ? ' is-invalid' : '');
$selectedEnrollmentId = (string) old('enrollment_id', $preselectedEnrollmentId);
@endphp

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.payments.index') }}">Pembayaran</a></li>
                <li aria-current="page">Catat Pembayaran</li>
            </ol>
            <h2>Catat Pembayaran</h2>
            <p>Transaksi yang dicatat berstatus Menunggu Verifikasi dan belum mengurangi tagihan sampai diverifikasi.</p>
        </div>
    </div>

    <x-admin.flash />

    @if ($enrollments->isEmpty())
        <div class="admin-alert is-error" role="alert" id="alert-no-enrollment">
            <x-admin.icon name="alert" :size="18" />
            <span>
                Tidak ada pendaftaran aktif yang masih memiliki sisa tagihan.
                <a href="{{ route('admin.enrollments.create') }}" style="color: inherit; font-weight: 700;">Buat pendaftaran terlebih dahulu</a>.
            </span>
        </div>
    @endif

    <section class="admin-panel">
        <form action="{{ route('admin.payments.store') }}" method="POST" enctype="multipart/form-data" class="admin-form" id="form-create-payment" novalidate>
            @csrf

            <div class="admin-form-section">
                <h3>Pendaftaran &amp; Nominal</h3>
                <p>Nominal tidak boleh melebihi sisa tagihan yang dapat dibayar.</p>

                <div class="admin-form-grid">
                    <div class="admin-field is-full">
                        <label for="enrollment_id" class="admin-label">Pendaftaran <span class="is-required" aria-hidden="true">*</span></label>
                        <select id="enrollment_id" name="enrollment_id" class="{{ $fieldClass('enrollment_id', 'admin-select') }}" required @disabled($enrollments->isEmpty())
                                @error('enrollment_id') aria-invalid="true" aria-describedby="enrollment_id-error" @enderror>
                            <option value="">— Pilih murid &amp; program —</option>
                            @foreach ($enrollments as $enrollmentOption)
                                <option value="{{ $enrollmentOption->id }}" @selected($selectedEnrollmentId === (string) $enrollmentOption->id)>
                                    {{ $enrollmentOption->student->name }} — {{ $enrollmentOption->program->name }} — dapat dibayar {{ Payment::rupiah($enrollmentOption->payableCents() / 100) }} dari {{ Payment::rupiah($enrollmentOption->bill_amount) }}
                                </option>
                            @endforeach
                        </select>
                        @error('enrollment_id') <p id="enrollment_id-error" class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="admin-field">
                        <label for="amount" class="admin-label">Nominal Dibayar (Rp) <span class="is-required" aria-hidden="true">*</span></label>
                        <input type="text" id="amount" name="amount" class="{{ $fieldClass('amount') }}" inputmode="decimal" value="{{ old('amount') }}" placeholder="Contoh: 350000" required
                               @error('amount') aria-invalid="true" aria-describedby="amount-error" @enderror>
                        @error('amount') <p id="amount-error" class="admin-error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="admin-field">
                        <label for="method" class="admin-label">Metode Pembayaran <span class="is-required" aria-hidden="true">*</span></label>
                        <select id="method" name="method" class="{{ $fieldClass('method', 'admin-select') }}" required
                                @error('method') aria-invalid="true" aria-describedby="method-error" @enderror>
                            <option value="">— Pilih metode —</option>
                            @foreach ($methods as $methodOption)
                                <option value="{{ $methodOption->value }}" @selected(old('method') === $methodOption->value)>{{ $methodOption->label() }}</option>
                            @endforeach
                        </select>
                        @error('method') <p id="method-error" class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="admin-field">
                        <label for="paid_on" class="admin-label">Tanggal Pembayaran <span class="is-required" aria-hidden="true">*</span></label>
                        <input type="date" id="paid_on" name="paid_on" class="{{ $fieldClass('paid_on') }}" value="{{ old('paid_on', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required
                               @error('paid_on') aria-invalid="true" aria-describedby="paid_on-error" @enderror>
                        @error('paid_on') <p id="paid_on-error" class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="admin-form-section">
                <h3>Bukti &amp; Catatan</h3>
                <p>Bukti disimpan secara privat dan hanya dapat dilihat oleh admin.</p>

                <div class="admin-form-grid">
                    <div class="admin-field">
                        <label for="proof" class="admin-label">Bukti Pembayaran <span class="is-optional">(opsional)</span></label>
                        <input type="file" id="proof" name="proof" class="{{ $fieldClass('proof') }}" accept=".jpg,.jpeg,.png,.pdf"
                               @error('proof') aria-invalid="true" @enderror aria-describedby="proof-hint{{ $errors->has('proof') ? ' proof-error' : '' }}">
                        <p id="proof-hint" class="admin-hint">JPG, PNG, atau PDF, maksimal {{ StorePaymentRequest::MAX_PROOF_KILOBYTES / 1024 }} MB.</p>
                        @error('proof') <p id="proof-error" class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="admin-field is-full">
                        <label for="notes" class="admin-label">Catatan Admin <span class="is-optional">(opsional)</span></label>
                        <textarea id="notes" name="notes" class="{{ $fieldClass('notes', 'admin-textarea') }}" maxlength="1000" rows="3"
                                  placeholder="Contoh: Transfer dari rekening orang tua a.n. Bambang"
                                  @error('notes') aria-invalid="true" aria-describedby="notes-error" @enderror>{{ old('notes') }}</textarea>
                        @error('notes') <p id="notes-error" class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="admin-form-actions">
                <a href="{{ route('admin.payments.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary" @disabled($enrollments->isEmpty())>
                    <x-admin.icon name="check" :size="16" />
                    Simpan Pembayaran
                </button>
            </div>
        </form>
    </section>
@endsection
