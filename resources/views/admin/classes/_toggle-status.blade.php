{{--
    Tombol aktifkan/nonaktifkan kelas.

    @var \App\Models\Classroom $classroom
    @var bool $isSmall
--}}
<form action="{{ route('admin.classes.toggle-status', $classroom) }}" method="POST"
      @if ($classroom->is_active)
          data-confirm="Nonaktifkan kelas {{ $classroom->name }}? Kelas tidak lagi menerima murid baru, tetapi datanya tetap tersimpan dan dapat diaktifkan kembali."
          data-confirm-title="Nonaktifkan Kelas"
          data-confirm-button="Ya, Nonaktifkan"
      @endif>
    @csrf
    @method('PATCH')
    <button type="submit" class="admin-btn admin-btn-secondary {{ $isSmall ? 'admin-btn-sm' : '' }}"
            aria-label="{{ $classroom->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $classroom->name }}">
        <x-admin.icon name="power" :size="$isSmall ? 14 : 16" />
        {{ $classroom->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
    </button>
</form>
