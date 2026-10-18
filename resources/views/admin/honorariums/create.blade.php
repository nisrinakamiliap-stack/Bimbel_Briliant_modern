@extends('layouts.admin')

@section('title', 'Catat Honorarium')
@section('page_title', 'Honorarium Guru')

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.honorariums.index') }}">Honorarium</a></li>
                <li aria-current="page">Catat Baru</li>
            </ol>
            <h2>Tetapkan Honorarium Guru</h2>
            <p>Tetapkan rincian honor mengajar guru untuk satu periode kalender berdasarkan kehadiran yang memenuhi syarat.</p>
        </div>
        <a href="{{ route('admin.honorariums.index') }}" class="admin-btn admin-btn-secondary">
            <x-admin.icon name="arrow-left" :size="16" />
            Kembali
        </a>
    </div>

    <x-admin.flash />

    <div class="admin-grid">
        <form method="POST" action="{{ route('admin.honorariums.store') }}" class="admin-panel is-wide" novalidate id="form-honorarium">
            @csrf

            <div class="admin-panel-header">
                <div>
                    <h3>Data Honorarium & Sesi Mengajar</h3>
                    <p>Pencatatan sesi mengajar berbasis kehadiran riil guru pada kelas aktif.</p>
                </div>
            </div>

            <div class="admin-form-body">
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="teacher_id" class="admin-label">Guru Pengajar <span class="admin-required">*</span></label>
                        <select id="teacher_id" name="teacher_id"
                            class="admin-input admin-select @error('teacher_id') is-error @enderror" required>
                            <option value="">Pilih guru</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id', $preselectedTeacherId) == $teacher->id)>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <p class="admin-hint is-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="period_month" class="admin-label">Bulan Periode <span class="admin-required">*</span></label>
                        <select id="period_month" name="period_month"
                            class="admin-input admin-select @error('period_month') is-error @enderror" required>
                            <option value="">Pilih bulan</option>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected(old('period_month', $preselectedMonth) == $m)>
                                    {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                        @error('period_month')
                            <p class="admin-hint is-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="admin-form-group">
                        <label for="period_year" class="admin-label">Tahun Periode <span class="admin-required">*</span></label>
                        <input type="number" id="period_year" name="period_year"
                            class="admin-input @error('period_year') is-error @enderror"
                            value="{{ old('period_year', $preselectedYear) }}"
                            min="2020" max="2099" required>
                        @error('period_year')
                            <p class="admin-hint is-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="session_count" class="admin-label">Jumlah Sesi Mengajar yang Diperhitungkan <span class="admin-required">*</span></label>
                        <div style="display: flex; gap: .5rem; align-items: center;">
                            <input type="number" id="session_count" name="session_count"
                                class="admin-input @error('session_count') is-error @enderror"
                                value="{{ old('session_count', $detectedSessions) }}"
                                min="0" max="9999" required style="max-width: 12rem;">
                            <button type="button" id="btn-detect-sessions" class="admin-btn admin-btn-secondary admin-btn-sm" title="Deteksi sesi dari data absensi guru pada periode ini">
                                Cek Absensi Riil
                            </button>
                        </div>
                        <p class="admin-hint" id="hint-sessions">
                            @if ($detectedSessions > 0)
                                Ditemukan <strong>{{ $detectedSessions }}</strong> sesi mengajar terverifikasi dari absensi pada periode ini.
                            @else
                                Jumlah sesi mengajar yang memenuhi syarat (Hadir / Terlambat) pada bulan tersebut.
                            @endif
                        </p>
                        @error('session_count')
                            <p class="admin-hint is-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="admin-form-group">
                        <label for="rate_per_session" class="admin-label">Tarif per Sesi (Rp) <span class="admin-required">*</span></label>
                        <input type="text" id="rate_per_session" name="rate_per_session"
                            class="admin-input @error('rate_per_session') is-error @enderror"
                            value="{{ old('rate_per_session') }}"
                            placeholder="Contoh: 75.000" required>
                        <p class="admin-hint">Tarif resmi yang ditetapkan lembaga Bimbel Sentra Edukasi.</p>
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
                            value="{{ old('adjustments', '0') }}"
                            placeholder="Contoh: 50.000">
                        <p class="admin-hint">Tambahan nominal yang disetujui (opsional, isi 0 jika tidak ada).</p>
                        @error('adjustments')
                            <p class="admin-hint is-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="admin-form-group">
                        <label for="deductions" class="admin-label">Potongan yang Disetujui (Rp)</label>
                        <input type="text" id="deductions" name="deductions"
                            class="admin-input @error('deductions') is-error @enderror"
                            value="{{ old('deductions', '0') }}"
                            placeholder="Contoh: 0">
                        <p class="admin-hint">Potongan resmi jika ada (opsional, isi 0 jika tidak ada).</p>
                        @error('deductions')
                            <p class="admin-hint is-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="notes" class="admin-label">Catatan Penetapan</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="admin-input @error('notes') is-error @enderror"
                        placeholder="Keterangan tambahan mengenai penetapan honorarium ini (opsional)">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="admin-hint is-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Simpan Penetapan (Status Draft)</button>
                <a href="{{ route('admin.honorariums.index') }}" class="admin-btn admin-btn-ghost">Batal</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const teacherSelect = document.getElementById('teacher_id');
            const monthSelect   = document.getElementById('period_month');
            const yearInput     = document.getElementById('period_year');
            const sessionInput  = document.getElementById('session_count');
            const btnDetect     = document.getElementById('btn-detect-sessions');
            const hintSessions  = document.getElementById('hint-sessions');

            function fetchSessions() {
                const teacherId = teacherSelect.value;
                const month     = monthSelect.value;
                const year      = yearInput.value;

                if (!teacherId || !month || !year) {
                    hintSessions.innerHTML = '<span style="color: var(--admin-muted);">Pilih guru, bulan, dan tahun terlebih dahulu.</span>';
                    return;
                }

                hintSessions.innerHTML = '<span style="color: var(--admin-muted);">Memeriksa riwayat kehadiran...</span>';

                fetch(`{{ route('admin.honorariums.check-sessions') }}?guru=${teacherId}&bulan=${month}&tahun=${year}`)
                    .then(res => res.json())
                    .then(data => {
                        sessionInput.value = data.sessions;
                        hintSessions.innerHTML = `Ditemukan <strong>${data.sessions}</strong> sesi mengajar terverifikasi dari absensi pada periode ini.`;
                    })
                    .catch(() => {
                        hintSessions.innerHTML = '<span class="admin-hint is-error">Gagal memeriksa kehadiran. Anda dapat mengisi sesi secara manual.</span>';
                    });
            }

            if (btnDetect) {
                btnDetect.addEventListener('click', fetchSessions);
            }
        });
    </script>
@endsection
