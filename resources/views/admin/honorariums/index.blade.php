@extends('layouts.admin')

@section('title', 'Honorarium Guru')
@section('page_title', 'Honorarium Guru')

@use('App\Models\TeacherHonorarium')

@section('content')
    <x-admin.flash />

    <div class="admin-page-header">
        <div>
            <h2>Honorarium Guru</h2>
            <p>Kelola penetapan honor mengajar guru per periode dan catat pembayaran bertahap maupun lunas.</p>
        </div>
        <a href="{{ route('admin.honorariums.create') }}" class="admin-btn admin-btn-primary" id="btn-add-honorarium">
            <x-admin.icon name="plus" :size="16" />
            Catat Honorarium
        </a>
    </div>

    <section class="admin-stats" aria-label="Ringkasan honorarium">
        <article class="admin-stat-card">
            <div class="admin-stat-icon is-green"><x-admin.icon name="check" :size="22" /></div>
            <div>
                <p class="admin-stat-label">Total Honor Dibayar</p>
                <p class="admin-stat-value">{{ TeacherHonorarium::rupiah($summary['paid_total']) }}</p>
                <p class="admin-stat-note">Seluruh pembayaran tercatat</p>
            </div>
        </article>
        <article class="admin-stat-card">
            <div class="admin-stat-icon is-amber"><x-admin.icon name="clock" :size="22" /></div>
            <div>
                <p class="admin-stat-label">Menunggu / Sisa Bayar</p>
                <p class="admin-stat-value">{{ TeacherHonorarium::rupiah($summary['approved_total']) }}</p>
                <p class="admin-stat-note">Honor disetujui yang belum lunas</p>
            </div>
        </article>
        <article class="admin-stat-card">
            <div class="admin-stat-icon is-navy"><x-admin.icon name="edit" :size="22" /></div>
            <div>
                <p class="admin-stat-label">Draft & Sebagian</p>
                <p class="admin-stat-value">{{ $summary['draft_count'] }} draft · {{ $summary['partial_count'] ?? 0 }} dicicil</p>
                <p class="admin-stat-note">Perlu tindakan admin</p>
            </div>
        </article>
    </section>

    <section class="admin-panel" aria-labelledby="honorariums-heading">
        <h3 id="honorariums-heading" class="sr-only">Daftar honorarium</h3>

        <form action="{{ route('admin.honorariums.index') }}" method="GET" class="admin-toolbar is-stacked" role="search">
            <div class="admin-toolbar-row">
                <select name="guru" class="admin-input admin-select" aria-label="Filter guru">
                    <option value="">Semua Guru</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(($teacherFilter?->id ?? null) === $teacher->id)>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>

                <select name="bulan" class="admin-input admin-select" aria-label="Filter bulan">
                    <option value="">Semua Bulan</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($monthFilter === $m)>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>

                <input type="number" name="tahun" class="admin-input" placeholder="Tahun"
                    value="{{ $yearFilter }}" min="2020" max="2099" aria-label="Filter tahun">

                <select name="status" class="admin-input admin-select" aria-label="Filter status">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected($statusFilter === $s)>{{ $s->label() }}</option>
                    @endforeach
                </select>

                <button type="submit" class="admin-btn admin-btn-secondary">Filter</button>
                @if ($teacherFilter || $monthFilter || $yearFilter || $statusFilter)
                    <a href="{{ route('admin.honorariums.index') }}" class="admin-btn admin-btn-ghost">Reset</a>
                @endif
            </div>
        </form>

        @if ($honorariums->isEmpty())
            <x-admin.empty-state title="Belum ada catatan honorarium" icon="payments">
                Klik "Catat Honorarium" untuk menetapkan honor guru per periode.
            </x-admin.empty-state>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Guru</th>
                            <th>Periode</th>
                            <th class="admin-table-num">Sesi</th>
                            <th class="admin-table-num">Total Honor</th>
                            <th class="admin-table-num">Sudah Dibayar</th>
                            <th class="admin-table-num">Sisa</th>
                            <th>Status</th>
                            <th class="admin-table-action"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($honorariums as $honorarium)
                            <tr>
                                <td>
                                    <strong>{{ $honorarium->teacher->name }}</strong>
                                </td>
                                <td>{{ $honorarium->periodLabel() }}</td>
                                <td class="admin-table-num">{{ $honorarium->session_count }} sesi</td>
                                <td class="admin-table-num">{{ TeacherHonorarium::rupiah($honorarium->amount) }}</td>
                                <td class="admin-table-num">{{ TeacherHonorarium::rupiah($honorarium->paid_amount) }}</td>
                                <td class="admin-table-num">
                                    @if ($honorarium->remainingAmount() > 0)
                                        <strong style="color: #92400e;">{{ TeacherHonorarium::rupiah($honorarium->remainingAmount()) }}</strong>
                                    @else
                                        <span class="admin-muted">Rp0</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="admin-badge {{ $honorarium->status->badgeClass() }}">
                                        {{ $honorarium->status->label() }}
                                    </span>
                                </td>
                                <td class="admin-table-action">
                                    <a href="{{ route('admin.honorariums.show', $honorarium) }}"
                                        class="admin-btn admin-btn-secondary admin-btn-sm">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($honorariums->hasPages())
                <div class="admin-pagination">
                    {{ $honorariums->links('admin.partials.pagination') }}
                </div>
            @endif
        @endif
    </section>
@endsection
