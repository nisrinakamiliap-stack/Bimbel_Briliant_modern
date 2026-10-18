{{--
    Tombol perubahan status yang diizinkan untuk sebuah pendaftaran.

    @var \App\Models\Enrollment $enrollment
    @var bool $isSmall
--}}
@php
$confirmations = [
    'approved' => ['Terima Pendaftaran', 'Ya, Terima', 'Terima pendaftaran :name ke program :program?'],
    'rejected' => ['Tolak Pendaftaran', 'Ya, Tolak', 'Tolak pendaftaran :name ke program :program? Data pendaftaran tetap tersimpan.'],
    'cancelled' => ['Batalkan Pendaftaran', 'Ya, Batalkan', 'Batalkan pendaftaran :name ke program :program? Data pendaftaran tetap tersimpan sebagai riwayat dan kursi kelas dilepas.'],
    'pending' => ['Buka Kembali Pendaftaran', 'Ya, Buka Kembali', 'Buka kembali pendaftaran :name ke program :program menjadi Menunggu Verifikasi? Pendaftaran ganda dan kapasitas kelas akan diperiksa ulang.'],
];
@endphp

@foreach ($enrollment->status->allowedTransitions() as $target)
    @php [$title, $button, $message] = $confirmations[$target->value]; @endphp
    <form action="{{ route('admin.enrollments.update-status', $enrollment) }}" method="POST"
          data-confirm="{{ str_replace([':name', ':program'], [$enrollment->student->name, $enrollment->program->name], $message) }}"
          data-confirm-title="{{ $title }}"
          data-confirm-button="{{ $button }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="{{ $target->value }}">
        <button type="submit"
                class="admin-btn {{ in_array($target->value, ['rejected', 'cancelled'], true) ? 'admin-btn-danger-outline' : ($target->value === 'approved' ? 'admin-btn-primary' : 'admin-btn-secondary') }} {{ $isSmall ? 'admin-btn-sm' : '' }}"
                aria-label="{{ $target->actionLabel() }} pendaftaran {{ $enrollment->student->name }}">
            {{ $target->actionLabel() }}
        </button>
    </form>
@endforeach
