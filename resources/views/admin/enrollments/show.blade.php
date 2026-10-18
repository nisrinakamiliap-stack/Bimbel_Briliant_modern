@extends('layouts.admin')

@section('title', 'Detail Pendaftaran — '.$enrollment->student->name)
@section('page_title', 'Pendaftaran Murid')

@php
$student = $enrollment->student;
$classroom = $enrollment->classroom;
@endphp

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.enrollments.index') }}">Pendaftaran Murid</a></li>
                <li aria-current="page">{{ $student->name }} · {{ $enrollment->program->name }}</li>
            </ol>
            <h2>Detail Pendaftaran</h2>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.enrollments.index') }}" class="admin-btn admin-btn-secondary">
                <x-admin.icon name="arrow-left" :size="16" />
                Kembali
            </a>
            <a href="{{ route('admin.enrollments.edit', $enrollment) }}" class="admin-btn admin-btn-primary">
                <x-admin.icon name="edit" :size="16" />
                Edit
            </a>
        </div>
    </div>

    <x-admin.flash />

    @foreach (['status', 'student_id', 'classroom_id'] as $statusErrorKey)
        @error($statusErrorKey)
            <div class="admin-alert is-error" role="alert">
                <x-admin.icon name="alert" :size="18" />
                <span>{{ $message }}</span>
            </div>
        @enderror
    @endforeach

    <div class="admin-grid">
        <section class="admin-panel is-wide" aria-labelledby="enrollment-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="enrollment-heading">{{ $enrollment->program->name }}</h3>
                    <p>Didaftarkan {{ $enrollment->enrolled_on->translatedFormat('d F Y') }}</p>
                </div>
                <span class="admin-badge {{ $enrollment->status->badgeClass() }}">{{ $enrollment->status->label() }}</span>
            </div>
            <dl class="admin-detail-list">
                <div>
                    <dt>Murid</dt>
                    <dd>
                        @if ($student->trashed())
                            {{ $student->name }} <span class="admin-badge is-danger">Akun dihapus</span>
                        @else
                            <a href="{{ route('admin.students.show', $student) }}" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;">{{ $student->name }}</a>
                        @endif
                        <span class="admin-cell-sub">{{ $student->email }} · {{ $student->phone ?? 'tanpa telepon' }}</span>
                    </dd>
                </div>
                <div>
                    <dt>Jenjang Murid</dt>
                    <dd>{{ $student->studentProfile?->education_level?->label() ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Program Bimbel</dt>
                    <dd>
                        <a href="{{ route('admin.programs.show', $enrollment->program) }}" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;">{{ $enrollment->program->name }}</a>
                        <span class="admin-cell-sub">{{ $enrollment->program->formattedPrice() }}</span>
                    </dd>
                </div>
                <div>
                    <dt>Kelas</dt>
                    <dd>
                        @if ($classroom)
                            <a href="{{ route('admin.classes.show', $classroom) }}" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;">{{ $classroom->name }}</a>
                            <span class="admin-cell-sub">
                                Guru: {{ $classroom->teacher?->name ?? 'belum ditentukan' }} · terisi {{ $classroomOccupancy }}/{{ $classroom->capacity }}
                            </span>
                        @else
                            <span class="admin-badge is-neutral">Belum ditentukan</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Tanggal Pendaftaran</dt>
                    <dd>{{ $enrollment->enrolled_on->translatedFormat('d F Y') }}</dd>
                </div>
                <div>
                    <dt>Status Terakhir Diubah</dt>
                    <dd>{{ $enrollment->status_changed_at?->translatedFormat('d F Y, H:i') ?? 'Belum pernah diubah' }}</dd>
                </div>
                <div style="grid-column: 1 / -1;">
                    <dt>Catatan Admin</dt>
                    <dd style="white-space: pre-line;">{{ $enrollment->admin_notes ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="admin-panel is-wide" aria-labelledby="billing-heading" id="panel-billing">
            <div class="admin-panel-header">
                <div>
                    <h3 id="billing-heading">Tagihan &amp; Pembayaran</h3>
                    <p>Hanya pembayaran berstatus Berhasil yang mengurangi sisa tagihan.</p>
                </div>
                <div class="admin-actions">
                    <span class="admin-badge {{ $enrollment->billingStatusBadgeClass() }}">{{ $enrollment->billingStatusLabel() }}</span>
                    @if ($enrollment->status->isActive() && $enrollment->payableCents() > 0)
                        <a href="{{ route('admin.payments.create', ['pendaftaran' => $enrollment->id]) }}" class="admin-btn admin-btn-primary admin-btn-sm" id="btn-record-payment">
                            <x-admin.icon name="plus" :size="14" /> Catat Pembayaran
                        </a>
                    @endif
                </div>
            </div>
            <dl class="admin-detail-list">
                <div>
                    <dt>Total Tagihan</dt>
                    <dd>{{ \App\Models\Payment::rupiah($enrollment->bill_amount) }}</dd>
                </div>
                <div>
                    <dt>Sudah Dibayar (Berhasil)</dt>
                    <dd>{{ \App\Models\Payment::rupiah($enrollment->paidCents() / 100) }}</dd>
                </div>
                <div>
                    <dt>Menunggu Verifikasi</dt>
                    <dd>{{ \App\Models\Payment::rupiah($enrollment->waitingCents() / 100) }}</dd>
                </div>
                <div>
                    <dt>Sisa Tagihan</dt>
                    <dd style="font-weight: 700;">{{ \App\Models\Payment::rupiah($enrollment->remainingCents() / 100) }}</dd>
                </div>
            </dl>
            @if ($enrollment->payments->isEmpty())
                <x-admin.empty-state title="Belum ada transaksi" icon="payments">
                    Pembayaran untuk pendaftaran ini belum dicatat.
                </x-admin.empty-state>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">No. Transaksi</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Metode</th>
                                <th scope="col">Nominal</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($enrollment->payments as $enrollmentPayment)
                                <tr>
                                    <td><a href="{{ route('admin.payments.show', $enrollmentPayment) }}" class="admin-cell-name" style="text-decoration: none;">{{ $enrollmentPayment->code }}</a></td>
                                    <td>{{ $enrollmentPayment->paid_on->translatedFormat('d M Y') }}</td>
                                    <td>{{ $enrollmentPayment->method->label() }}</td>
                                    <td>{{ \App\Models\Payment::rupiah($enrollmentPayment->amount) }}</td>
                                    <td><span class="admin-badge {{ $enrollmentPayment->status->badgeClass() }}">{{ $enrollmentPayment->status->label() }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-panel is-wide" aria-labelledby="status-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="status-heading">Ubah Status</h3>
                    <p>
                        @switch($enrollment->status->value)
                            @case('pending') Verifikasi pendaftaran: terima, tolak, atau batalkan. @break
                            @case('approved') Pendaftaran diterima. Dapat dibatalkan jika murid mengundurkan diri. @break
                            @default Pendaftaran tidak aktif dan tetap tersimpan sebagai riwayat. Dapat dibuka kembali secara manual.
                        @endswitch
                    </p>
                </div>
                <div class="admin-actions" style="flex-wrap: wrap;">
                    @include('admin.enrollments._status-actions', ['enrollment' => $enrollment, 'isSmall' => false])
                </div>
            </div>
        </section>
    </div>
@endsection
