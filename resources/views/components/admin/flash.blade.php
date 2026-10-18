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

@if ($errors->any())
    <div class="admin-alert is-error" role="alert">
        <x-admin.icon name="alert" :size="18" />
        <span>Data belum dapat disimpan. Periksa kembali isian yang ditandai merah.</span>
    </div>
@endif
