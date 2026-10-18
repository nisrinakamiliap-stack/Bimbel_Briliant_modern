{{-- Navigasi internal modul Pembelajaran. --}}
@php
$learningTabs = [
    ['label' => 'Materi Pembelajaran', 'route' => 'admin.learning.materials.index', 'active' => 'admin.learning.materials.*'],
    ['label' => 'Tugas', 'route' => 'admin.learning.assignments.index', 'active' => 'admin.learning.assignments.*'],
    ['label' => 'Pengumpulan Tugas', 'route' => 'admin.learning.submissions.index', 'active' => 'admin.learning.submissions.*'],
    ['label' => 'Penilaian Tugas', 'route' => 'admin.learning.grades.index', 'active' => 'admin.learning.grades.*'],
];
@endphp
<nav class="admin-view-switch" aria-label="Bagian modul Pembelajaran" style="margin-bottom: 1.25rem; flex-wrap: wrap;">
    @foreach ($learningTabs as $tab)
        @php $isActiveTab = request()->routeIs($tab['active']); @endphp
        <a href="{{ route($tab['route']) }}" class="{{ $isActiveTab ? 'is-active' : '' }}" @if ($isActiveTab) aria-current="page" @endif>{{ $tab['label'] }}</a>
    @endforeach
</nav>
