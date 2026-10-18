{{--
    Field form pendaftaran yang dipakai bersama oleh halaman tambah dan edit.

    @var \App\Models\Enrollment|null $enrollment
    @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $students
    @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Program> $programs
    @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
    @var int|null $preselectedStudentId
--}}
@php
$fieldClass = fn (string $field, string $base = 'admin-input'): string => $base.($errors->has($field) ? ' is-invalid' : '');
$describedBy = fn (string $field, bool $hasHint = false): string => collect([
    $hasHint ? $field.'-hint' : null,
    $errors->has($field) ? $field.'-error' : null,
])->filter()->implode(' ');

$editable = $enrollment?->editableFields() ?? ['student_id', 'program_id', 'classroom_id', 'enrolled_on', 'admin_notes'];
$canEdit = fn (string $field): bool => in_array($field, $editable, true);

$selectedStudentId = (string) old('student_id', $preselectedStudentId);
$selectedProgramId = (string) old('program_id', $enrollment?->program_id);
$selectedClassroomId = (string) old('classroom_id', $enrollment?->classroom_id);
$classroomsByProgram = $classrooms->groupBy('program_id');
@endphp

<div class="admin-form-section">
    <h3>Murid &amp; Program</h3>
    <p>Kelas boleh dikosongkan dan ditentukan setelah pendaftaran diverifikasi.</p>

    <div class="admin-form-grid">
        <div class="admin-field is-full">
            <label for="student_id" class="admin-label">Murid <span class="is-required" aria-hidden="true">*</span></label>
            @if ($enrollment === null)
                <select id="student_id" name="student_id" class="{{ $fieldClass('student_id', 'admin-select') }}" required
                        @disabled($students->isEmpty())
                        @error('student_id') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('student_id', true) }}">
                    <option value="">— Pilih murid —</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected($selectedStudentId === (string) $student->id)>{{ $student->name }} — {{ $student->email }}</option>
                    @endforeach
                </select>
                <p id="student_id-hint" class="admin-hint">Pilih dari akun murid yang sudah terdaftar. Akun baru dibuat melalui menu Data Murid.</p>
                @error('student_id') <p id="student_id-error" class="admin-error" role="alert">{{ $message }}</p> @enderror
            @else
                <input type="text" id="student_id" class="admin-input" value="{{ $enrollment->student->name }} — {{ $enrollment->student->email }}" disabled>
                <p class="admin-hint">Murid tidak dapat diganti. Buat pendaftaran baru jika murid berbeda.</p>
            @endif
        </div>

        <div class="admin-field">
            <label for="program_id" class="admin-label">Program Bimbel <span class="is-required" aria-hidden="true">*</span></label>
            @if ($canEdit('program_id'))
                <select id="program_id" name="program_id" class="{{ $fieldClass('program_id', 'admin-select') }}" required
                        @disabled($programs->isEmpty())
                        @error('program_id') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('program_id') }}">
                    <option value="">— Pilih program —</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" @selected($selectedProgramId === (string) $program->id)>{{ $program->name }}{{ $program->is_active ? '' : ' (tidak aktif)' }}</option>
                    @endforeach
                </select>
                @error('program_id') <p id="program_id-error" class="admin-error">{{ $message }}</p> @enderror
            @else
                <input type="text" id="program_id" class="admin-input" value="{{ $enrollment->program->name }}" disabled data-program-id="{{ $enrollment->program_id }}">
                <p class="admin-hint">Program tidak dapat diganti setelah pendaftaran {{ strtolower($enrollment->status->label()) }}.</p>
            @endif
        </div>

        <div class="admin-field">
            <label for="classroom_id" class="admin-label">Kelas <span class="is-optional">(opsional)</span></label>
            @if ($canEdit('classroom_id'))
                <select id="classroom_id" name="classroom_id" class="{{ $fieldClass('classroom_id', 'admin-select') }}"
                        @error('classroom_id') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('classroom_id', true) }}">
                    <option value="">— Belum ditentukan —</option>
                    @foreach ($programs as $program)
                        @if ($classroomsByProgram->has($program->id))
                            <optgroup label="{{ $program->name }}" data-program-id="{{ $program->id }}">
                                @foreach ($classroomsByProgram->get($program->id) as $classroom)
                                    @php $isFull = $classroom->occupied_seats >= $classroom->capacity && (string) $classroom->id !== (string) $enrollment?->classroom_id; @endphp
                                    <option value="{{ $classroom->id }}" @selected($selectedClassroomId === (string) $classroom->id)>
                                        {{ $classroom->name }} — terisi {{ $classroom->occupied_seats }}/{{ $classroom->capacity }}{{ $isFull ? ' (penuh)' : '' }}{{ $classroom->is_active ? '' : ' (tidak aktif)' }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                <p id="classroom_id-hint" class="admin-hint">Hanya kelas dari program yang dipilih. Kapasitas diperiksa saat disimpan.</p>
                @error('classroom_id') <p id="classroom_id-error" class="admin-error" role="alert">{{ $message }}</p> @enderror
            @else
                <input type="text" id="classroom_id" class="admin-input" value="{{ $enrollment->classroom?->name ?? 'Belum ditentukan' }}" disabled>
            @endif
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3>Detail Pendaftaran</h3>
    <p>Tanggal pendaftaran dan catatan internal admin.</p>

    <div class="admin-form-grid">
        <div class="admin-field">
            <label for="enrolled_on" class="admin-label">Tanggal Pendaftaran <span class="is-required" aria-hidden="true">*</span></label>
            @if ($canEdit('enrolled_on'))
                <input type="date" id="enrolled_on" name="enrolled_on" class="{{ $fieldClass('enrolled_on') }}" required
                       value="{{ old('enrolled_on', $enrollment?->enrolled_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}"
                       @error('enrolled_on') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('enrolled_on') }}">
                @error('enrolled_on') <p id="enrolled_on-error" class="admin-error">{{ $message }}</p> @enderror
            @else
                <input type="text" id="enrolled_on" class="admin-input" value="{{ $enrollment->enrolled_on->translatedFormat('d F Y') }}" disabled>
            @endif
        </div>

        <div class="admin-field">
            <label for="bill_amount" class="admin-label">Total Tagihan (Rp)</label>
            @if ($enrollment !== null && $canEdit('bill_amount'))
                <input type="text" id="bill_amount" name="bill_amount" class="{{ $fieldClass('bill_amount') }}" inputmode="decimal"
                       value="{{ old('bill_amount', rtrim(rtrim((string) $enrollment->bill_amount, '0'), '.')) }}" required
                       @error('bill_amount') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('bill_amount', true) }}">
                <p id="bill_amount-hint" class="admin-hint">Dapat disesuaikan (mis. diskon), minimal sebesar pembayaran yang sudah berhasil. Mengganti program akan mengatur ulang tagihan ke harga program baru.</p>
                @error('bill_amount') <p id="bill_amount-error" class="admin-error">{{ $message }}</p> @enderror
            @elseif ($enrollment !== null)
                <input type="text" id="bill_amount" class="admin-input" value="{{ \App\Models\Payment::rupiah($enrollment->bill_amount) }}" disabled>
            @else
                <input type="text" id="bill_amount" class="admin-input" value="Mengikuti harga program yang dipilih" disabled>
            @endif
        </div>

        @if ($enrollment !== null)
            <div class="admin-field">
                <label for="status-readonly" class="admin-label">Status</label>
                <input type="text" id="status-readonly" class="admin-input" value="{{ $enrollment->status->label() }}" disabled>
                <p class="admin-hint">Status diubah melalui tombol aksi di halaman detail.</p>
            </div>
        @endif

        <div class="admin-field is-full">
            <label for="admin_notes" class="admin-label">Catatan Admin <span class="is-optional">(opsional)</span></label>
            <textarea id="admin_notes" name="admin_notes" class="{{ $fieldClass('admin_notes', 'admin-textarea') }}" maxlength="2000" rows="3"
                      @error('admin_notes') aria-invalid="true" @enderror aria-describedby="{{ $describedBy('admin_notes') }}">{{ old('admin_notes', $enrollment?->admin_notes) }}</textarea>
            @error('admin_notes') <p id="admin_notes-error" class="admin-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';
    var programField = document.getElementById('program_id');
    var classroomSelect = document.getElementById('classroom_id');
    if (!programField || !classroomSelect || classroomSelect.tagName !== 'SELECT') return;

    function selectedProgramId() {
        return programField.tagName === 'SELECT' ? programField.value : programField.getAttribute('data-program-id');
    }

    function filterClassrooms() {
        var programId = selectedProgramId();
        classroomSelect.querySelectorAll('optgroup').forEach(function (group) {
            var matches = group.getAttribute('data-program-id') === programId;
            group.hidden = !matches;
            group.disabled = !matches;
        });
        var selected = classroomSelect.selectedOptions[0];
        if (selected && selected.parentElement.tagName === 'OPTGROUP' && selected.parentElement.disabled) {
            classroomSelect.value = '';
        }
    }

    if (programField.tagName === 'SELECT') programField.addEventListener('change', filterClassrooms);
    filterClassrooms();
}());
</script>
@endpush
