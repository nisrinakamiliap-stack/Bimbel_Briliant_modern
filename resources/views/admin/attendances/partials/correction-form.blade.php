{{--
    Form koreksi status absensi (dipakai absensi murid dan guru).

    @var \App\Models\StudentAttendance|\App\Models\TeacherAttendance $attendance
    @var array<int, \BackedEnum> $statuses
    @var string $action
    @var string $cancelUrl
    @var array<string, string> $details
--}}
<div class="admin-grid">
    <section class="admin-panel" aria-labelledby="attendance-detail-heading">
        <div class="admin-panel-header">
            <div>
                <h3 id="attendance-detail-heading">Catatan Saat Ini</h3>
            </div>
            <span class="admin-badge {{ $attendance->status->badgeClass() }}">{{ $attendance->status->label() }}</span>
        </div>
        <dl class="admin-detail-list">
            @foreach ($details as $label => $value)
                <div>
                    <dt>{{ $label }}</dt>
                    <dd>{{ $value }}</dd>
                </div>
            @endforeach
            @if ($attendance->wasCorrected())
                <div style="grid-column: 1 / -1;">
                    <dt>Koreksi Terakhir</dt>
                    <dd>
                        {{ $attendance->previous_status?->label() }} → {{ $attendance->status->label() }}
                        oleh {{ $attendance->corrector?->name ?? '—' }} pada {{ $attendance->corrected_at->translatedFormat('d M Y, H:i') }}
                        <span class="admin-cell-sub">Alasan: {{ $attendance->correction_reason }}</span>
                    </dd>
                </div>
            @endif
        </dl>
    </section>

    <section class="admin-panel" aria-labelledby="correction-heading">
        <div class="admin-panel-header">
            <div>
                <h3 id="correction-heading">Koreksi Status</h3>
                <p>Catatan tidak dihapus; perubahan tercatat dan dapat ditelusuri.</p>
            </div>
        </div>
        <form action="{{ $action }}" method="POST" class="admin-form" id="form-correct-attendance"
              data-confirm="Simpan koreksi status kehadiran ini? Status sebelumnya dan alasan koreksi akan tercatat."
              data-confirm-title="Koreksi Absensi" data-confirm-button="Ya, Simpan Koreksi">
            @csrf
            @method('PUT')
            <div class="admin-form-grid" style="grid-template-columns: 1fr;">
                <div class="admin-field">
                    <label for="status" class="admin-label">Status yang Benar <span class="is-required" aria-hidden="true">*</span></label>
                    <select id="status" name="status" class="admin-select {{ $errors->has('status') ? 'is-invalid' : '' }}" required
                            @error('status') aria-invalid="true" aria-describedby="status-error" @enderror>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption->value }}" @selected(old('status', $attendance->status->value) === $statusOption->value)>{{ $statusOption->label() }}</option>
                        @endforeach
                    </select>
                    @error('status') <p id="status-error" class="admin-error">{{ $message }}</p> @enderror
                </div>
                <div class="admin-field">
                    <label for="reason" class="admin-label">Alasan Koreksi <span class="is-required" aria-hidden="true">*</span></label>
                    <textarea id="reason" name="reason" class="admin-textarea {{ $errors->has('reason') ? 'is-invalid' : '' }}" rows="3" maxlength="500" required
                              placeholder="Contoh: Murid menyerahkan surat sakit pada hari berikutnya."
                              @error('reason') aria-invalid="true" aria-describedby="reason-error" @enderror>{{ old('reason') }}</textarea>
                    @error('reason') <p id="reason-error" class="admin-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="admin-form-actions">
                <a href="{{ $cancelUrl }}" class="admin-btn admin-btn-secondary">Batal</a>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-admin.icon name="check" :size="16" /> Simpan Koreksi
                </button>
            </div>
        </form>
    </section>
</div>
