{{-- Navigasi internal Manajemen Murid. --}}
@php
$isHistory = request()->routeIs('admin.enrollments.index') && request()->boolean('riwayat');
$studentTabs = [
    ['label' => 'Data Murid', 'url' => route('admin.students.index'), 'active' => request()->routeIs('admin.students.*')],
    ['label' => 'Pendaftaran', 'url' => route('admin.enrollments.index'), 'active' => request()->routeIs('admin.enrollments.*') && ! $isHistory],
    ['label' => 'Riwayat Pendaftaran', 'url' => route('admin.enrollments.index', ['riwayat' => 1]), 'active' => $isHistory],
];
@endphp
<nav class="admin-view-switch" aria-label="Bagian Manajemen Murid" style="margin-bottom: 1.25rem; flex-wrap: wrap;">
    @foreach ($studentTabs as $tab)
        <a href="{{ $tab['url'] }}" class="{{ $tab['active'] ? 'is-active' : '' }}" @if ($tab['active']) aria-current="page" @endif>{{ $tab['label'] }}</a>
    @endforeach
</nav>
