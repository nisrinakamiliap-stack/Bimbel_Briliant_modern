@if (session('success'))
    <div class="admin-alert" role="status">
        <x-admin.icon name="check" :size="18" />
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="admin-alert is-error" role="alert">
        <x-admin.icon name="alert" :size="18" />
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- Galat check-in/check-out guru tidak terkait isian formulir, jadi pesannya ditampilkan langsung. --}}
@error('schedule')
    <div class="admin-alert is-error" role="alert">
        <x-admin.icon name="alert" :size="18" />
        <span>{{ $message }}</span>
    </div>
@enderror

@if (collect($errors->keys())->reject(fn (string $key) => $key === 'schedule')->isNotEmpty())
    <div class="admin-alert is-error" role="alert">
        <x-admin.icon name="alert" :size="18" />
        <span>Data belum dapat disimpan. Periksa kembali isian yang ditandai merah.</span>
    </div>
@endif
