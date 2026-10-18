{{--
    Tombol aktifkan/nonaktifkan jadwal.

    @var \App\Models\Schedule $schedule
    @var bool $isSmall
--}}
<form action="{{ route('admin.schedules.toggle-status', $schedule) }}" method="POST"
      @if ($schedule->is_active)
          data-confirm="Nonaktifkan jadwal {{ $schedule->classroom->name }} pada {{ $schedule->summary() }}? Riwayat pembelajaran tetap tersimpan dan jadwal dapat diaktifkan kembali."
          data-confirm-title="Nonaktifkan Jadwal"
          data-confirm-button="Ya, Nonaktifkan"
      @endif>
    @csrf
    @method('PATCH')
    <button type="submit" class="admin-btn admin-btn-secondary {{ $isSmall ? 'admin-btn-sm' : '' }}"
            aria-label="{{ $schedule->is_active ? 'Nonaktifkan' : 'Aktifkan' }} jadwal {{ $schedule->classroom->name }} {{ $schedule->summary() }}">
        <x-admin.icon name="power" :size="$isSmall ? 14 : 16" />
        {{ $schedule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
    </button>
</form>
