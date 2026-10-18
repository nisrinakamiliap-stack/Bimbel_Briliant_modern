@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Admin')

@section('content')
    @if (session('success'))
        <div class="admin-alert" role="status">{{ session('success') }}</div>
    @endif

    <section class="admin-welcome" aria-labelledby="welcome-heading">
        <h2 id="welcome-heading">Selamat datang, {{ auth()->user()->name }}</h2>
        <p>Ringkasan aktivitas Bimbel Sentra Edukasi per {{ now()->translatedFormat('l, d F Y') }}.</p>
    </section>

    @if (($honorariumStats['pending_approval'] ?? 0) > 0 || ($honorariumStats['pending_payment'] ?? 0) > 0)
        <div class="admin-alert" style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; background-color: var(--admin-mint-bg); border-left: 4px solid var(--admin-primary); color: var(--admin-forest);">
            <div style="display: flex; align-items: center; gap: .75rem;">
                <x-admin.icon name="payments" :size="20" />
                <span>
                    Terdapat
                    @if ($honorariumStats['pending_approval'] > 0)
                        <strong>{{ $honorariumStats['pending_approval'] }}</strong> honorarium berstatus draft
                    @endif
                    @if ($honorariumStats['pending_approval'] > 0 && $honorariumStats['pending_payment'] > 0)
                        dan
                    @endif
                    @if ($honorariumStats['pending_payment'] > 0)
                        <strong>{{ $honorariumStats['pending_payment'] }}</strong> honorarium menunggu/belum lunas dibayar.
                    @endif
                </span>
            </div>
            <a href="{{ route('admin.honorariums.index') }}" class="admin-btn admin-btn-sm admin-btn-primary">Kelola Honorarium</a>
        </div>
    @endif

    {{-- KARTU STATISTIK --}}
    <section class="admin-stats" aria-label="Statistik utama">
        <x-admin.stat-card id="stat-students" label="Total Murid" :value="$stats['students']" icon="students" tone="green" note="Akun murid terdaftar" />
        <x-admin.stat-card id="stat-teachers" label="Total Guru" :value="$stats['teachers']" icon="teachers" tone="navy" note="Akun guru/tentor" />
        <x-admin.stat-card id="stat-programs" label="Total Program Bimbel" :value="$stats['programs']" icon="programs" tone="amber" :note="$stats['active_programs'].' program aktif'" />
        <x-admin.stat-card id="stat-classes" label="Total Kelas" :value="$stats['classes']" icon="classes" tone="sky" :note="$stats['active_classes'].' kelas aktif'" />
    </section>

    <div class="admin-grid">
        {{-- PENDAFTARAN TERBARU --}}
        <section class="admin-panel is-wide" aria-labelledby="enrollments-heading" id="panel-enrollments">
            <div class="admin-panel-header">
                <div>
                    <h3 id="enrollments-heading">Pendaftaran Terbaru</h3>
                    <p>Pendaftaran murid ke program bimbel.</p>
                </div>
            </div>

            @if ($recentEnrollments->isEmpty())
                <x-admin.empty-state title="Belum ada pendaftaran" icon="enrollments">
                    Pendaftaran murid ke program bimbel akan tampil di sini. Tambahkan melalui menu Pendaftaran.
                </x-admin.empty-state>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Nama Murid</th>
                                <th scope="col">Program Bimbel</th>
                                <th scope="col">Tanggal Pendaftaran</th>
                                <th scope="col">Status</th>
                                <th scope="col"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentEnrollments as $enrollment)
                                <tr>
                                    <td>{{ $enrollment['student_name'] }}</td>
                                    <td>{{ $enrollment['program'] }}</td>
                                    <td>{{ $enrollment['registered_at']->translatedFormat('d M Y') }}</td>
                                    <td>
                                        <span class="admin-badge {{ $enrollment['badge'] }}">{{ $enrollment['status'] }}</span>
                                    </td>
                                    <td>
                                        @if ($enrollment['detail_url'])
                                            <a href="{{ $enrollment['detail_url'] }}" class="admin-badge is-neutral">Detail</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- PEMBAYARAN MENUNGGU VERIFIKASI --}}
        <section class="admin-panel" aria-labelledby="payments-heading" id="panel-payments">
            <div class="admin-panel-header">
                <div>
                    <h3 id="payments-heading">Pembayaran Menunggu Verifikasi</h3>
                    <p>Bukti pembayaran yang perlu diperiksa.</p>
                </div>
            </div>

            @if ($pendingPayments->isEmpty())
                <x-admin.empty-state title="Tidak ada pembayaran tertunda" icon="payments">
                    Semua pembayaran yang dicatat sudah diverifikasi atau ditolak.
                </x-admin.empty-state>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Nama Murid</th>
                                <th scope="col">Nominal</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingPayments as $payment)
                                <tr>
                                    <td><a href="{{ $payment['detail_url'] }}" class="admin-cell-name" style="text-decoration: none;">{{ $payment['student_name'] }}</a></td>
                                    <td>{{ \App\Models\Payment::rupiah($payment['amount']) }}</td>
                                    <td>{{ $payment['paid_at']->translatedFormat('d M Y') }}</td>
                                    <td>
                                        <span class="admin-badge {{ $payment['badge'] }}">{{ $payment['status'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- JADWAL MENDATANG --}}
        <section class="admin-panel" aria-labelledby="schedules-heading" id="panel-schedules">
            <div class="admin-panel-header">
                <div>
                    <h3 id="schedules-heading">Jadwal Mendatang</h3>
                    <p>Sesi pembelajaran terdekat.</p>
                </div>
            </div>

            @if ($upcomingSchedules->isEmpty())
                <x-admin.empty-state title="Belum ada jadwal mendatang" icon="clock">
                    Belum ada jadwal aktif untuk kelas aktif. Tambahkan jadwal melalui menu Jadwal.
                </x-admin.empty-state>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Sesi</th>
                                <th scope="col">Program</th>
                                <th scope="col">Guru</th>
                                <th scope="col">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($upcomingSchedules as $schedule)
                                <tr>
                                    <td>{{ $schedule['title'] }}</td>
                                    <td>{{ $schedule['program'] }}</td>
                                    <td>{{ $schedule['teacher'] }}</td>
                                    <td>{{ $schedule['starts_at']->translatedFormat('d M Y, H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
