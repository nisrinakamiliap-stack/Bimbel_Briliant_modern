@extends('layouts.admin')

@section('title', 'Pembayaran')
@section('page_title', 'Pembayaran')

@use('App\Models\Payment')

@php
$hasFilters = $search !== '' || $status || $method || $dateFrom || $dateTo || $enrollmentFilter || $studentFilterName;
@endphp

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Pembayaran</h2>
            <p>Catat pembayaran manual dan verifikasi transaksi murid. Pembayaran otomatis (payment gateway) belum tersedia.</p>
        </div>
        <a href="{{ route('admin.payments.create') }}" class="admin-btn admin-btn-primary" id="btn-add-payment">
            <x-admin.icon name="plus" :size="16" />
            Catat Pembayaran
        </a>
    </div>

    <section class="admin-stats" aria-label="Ringkasan pembayaran">
        <article class="admin-stat-card" id="summary-paid">
            <div class="admin-stat-icon is-green"><x-admin.icon name="check" :size="22" /></div>
            <div>
                <p class="admin-stat-label">Total Pembayaran Berhasil</p>
                <p class="admin-stat-value">{{ Payment::rupiah($summary['paid_total']) }}</p>
                <p class="admin-stat-note">Seluruh transaksi terverifikasi</p>
            </div>
        </article>
        <article class="admin-stat-card" id="summary-waiting">
            <div class="admin-stat-icon is-amber"><x-admin.icon name="clock" :size="22" /></div>
            <div>
                <p class="admin-stat-label">Menunggu Verifikasi</p>
                <p class="admin-stat-value">{{ $summary['waiting_count'] }} transaksi</p>
                <p class="admin-stat-note">Senilai {{ Payment::rupiah($summary['waiting_total']) }}</p>
            </div>
        </article>
        <article class="admin-stat-card" id="summary-outstanding">
            <div class="admin-stat-icon is-navy"><x-admin.icon name="payments" :size="22" /></div>
            <div>
                <p class="admin-stat-label">Tagihan Belum Lunas</p>
                <p class="admin-stat-value">{{ $summary['outstanding_count'] }} pendaftaran</p>
                <p class="admin-stat-note">Sisa {{ Payment::rupiah($summary['outstanding_total']) }}</p>
            </div>
        </article>
    </section>

    <section class="admin-panel" aria-labelledby="payments-heading">
        <h3 id="payments-heading" class="sr-only">Daftar transaksi</h3>

        <form action="{{ route('admin.payments.index') }}" method="GET" class="admin-toolbar is-stacked" role="search">
            @if ($enrollmentFilter)
                <input type="hidden" name="pendaftaran" value="{{ $enrollmentFilter->id }}">
                <p class="admin-hint" style="margin: 0;">Riwayat pendaftaran: <strong>{{ $enrollmentFilter->student->name }} — {{ $enrollmentFilter->program->name }}</strong></p>
            @endif
            @if ($studentFilterName)
                <input type="hidden" name="murid" value="{{ request('murid') }}">
                <p class="admin-hint" style="margin: 0;">Riwayat murid: <strong>{{ $studentFilterName }}</strong></p>
            @endif
            <div class="admin-toolbar-search">
                <label for="q" class="sr-only">Cari nama murid atau nomor transaksi</label>
                <x-admin.icon name="search" :size="16" />
                <input type="search" id="q" name="q" value="{{ $search }}" class="admin-input" placeholder="Cari nama/email murid atau nomor transaksi..." maxlength="100">
            </div>
            <div class="admin-toolbar-row">
                <label for="status" class="sr-only">Filter status</label>
                <select id="status" name="status" class="admin-select">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected($status === $statusOption)>{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
                <label for="metode" class="sr-only">Filter metode</label>
                <select id="metode" name="metode" class="admin-select">
                    <option value="">Semua metode</option>
                    @foreach ($methods as $methodOption)
                        <option value="{{ $methodOption->value }}" @selected($method === $methodOption)>{{ $methodOption->label() }}</option>
                    @endforeach
                </select>
                <label for="dari" class="sr-only">Periode dari</label>
                <input type="date" id="dari" name="dari" value="{{ $dateFrom }}" class="admin-input" style="flex: 1; min-width: 10rem;" title="Periode dari">
                <label for="sampai" class="sr-only">Periode sampai</label>
                <input type="date" id="sampai" name="sampai" value="{{ $dateTo }}" class="admin-input" style="flex: 1; min-width: 10rem;" title="Periode sampai">
                <button type="submit" class="admin-btn admin-btn-secondary">Terapkan</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.payments.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                @endif
            </div>
            @error('sampai') <p class="admin-error" style="margin: 0;">{{ $message }}</p> @enderror
        </form>

        @if ($payments->isEmpty())
            @if ($hasFilters)
                <x-admin.empty-state title="Transaksi tidak ditemukan" icon="search">
                    Tidak ada transaksi yang cocok dengan pencarian atau filter Anda.
                </x-admin.empty-state>
            @else
                <x-admin.empty-state title="Belum ada transaksi" icon="payments">
                    Catat pembayaran murid melalui tombol Catat Pembayaran atau dari halaman detail pendaftaran.
                </x-admin.empty-state>
            @endif
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">No. Transaksi</th>
                            <th scope="col">Nama Murid</th>
                            <th scope="col">Program / Pendaftaran</th>
                            <th scope="col">Nominal</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td class="is-muted">{{ $payments->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="admin-cell-name">{{ $payment->code }}</span>
                                    <span class="admin-cell-sub">{{ $payment->method->label() }}</span>
                                </td>
                                <td>{{ $payment->enrollment->student->name }}</td>
                                <td>
                                    <a href="{{ route('admin.enrollments.show', $payment->enrollment) }}" style="color: var(--admin-primary); text-decoration: none;">{{ $payment->enrollment->program->name }}</a>
                                </td>
                                <td>{{ Payment::rupiah($payment->amount) }}</td>
                                <td>{{ $payment->paid_on->translatedFormat('d M Y') }}</td>
                                <td><span class="admin-badge {{ $payment->status->badgeClass() }}">{{ $payment->status->label() }}</span></td>
                                <td>
                                    <a href="{{ route('admin.payments.show', $payment) }}" class="admin-btn {{ $payment->status->isProcessable() ? 'admin-btn-primary' : 'admin-btn-secondary' }} admin-btn-sm" aria-label="{{ $payment->status->isProcessable() ? 'Periksa' : 'Detail' }} transaksi {{ $payment->code }}">
                                        <x-admin.icon name="eye" :size="14" /> {{ $payment->status->isProcessable() ? 'Periksa' : 'Detail' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                {{ $payments->links('admin.partials.pagination') }}
            @else
                <p class="admin-result-info">Menampilkan {{ $payments->total() }} transaksi.</p>
            @endif
        @endif
    </section>
@endsection
