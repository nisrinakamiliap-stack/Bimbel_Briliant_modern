{{--
    Tombol aktifkan/nonaktifkan program.

    @var \App\Models\Program $program
    @var bool $isSmall
--}}
<form action="{{ route('admin.programs.toggle-status', $program) }}" method="POST"
      @if ($program->is_active)
          data-confirm="Nonaktifkan program {{ $program->name }}? Program tidak lagi ditawarkan, tetapi datanya tetap tersimpan dan dapat diaktifkan kembali."
          data-confirm-title="Nonaktifkan Program"
          data-confirm-button="Ya, Nonaktifkan"
      @endif>
    @csrf
    @method('PATCH')
    <button type="submit" class="admin-btn admin-btn-secondary {{ $isSmall ? 'admin-btn-sm' : '' }}"
            aria-label="{{ $program->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $program->name }}">
        <x-admin.icon name="power" :size="$isSmall ? 14 : 16" />
        {{ $program->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
    </button>
</form>
