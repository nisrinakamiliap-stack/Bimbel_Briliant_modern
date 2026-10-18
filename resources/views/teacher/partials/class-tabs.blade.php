{{-- Navigasi internal menu Kelas & Jadwal. --}}
@php
$classTabs = [
    ['label' => 'Jadwal Mengajar', 'route' => 'teacher.schedules.index', 'active' => 'teacher.schedules.*'],
    ['label' => 'Kelas Saya', 'route' => 'teacher.classes.index', 'active' => 'teacher.classes.*'],
];
@endphp
<nav class="admin-view-switch" aria-label="Bagian Kelas &amp; Jadwal" style="margin-bottom: 1.25rem; flex-wrap: wrap;">
    @foreach ($classTabs as $tab)
        @php $isActiveTab = request()->routeIs($tab['active']); @endphp
        <a href="{{ route($tab['route']) }}" class="{{ $isActiveTab ? 'is-active' : '' }}" @if ($isActiveTab) aria-current="page" @endif>{{ $tab['label'] }}</a>
    @endforeach
</nav>
