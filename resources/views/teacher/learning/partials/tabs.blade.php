{{-- Navigasi internal Pembelajaran, sama dengan isi dropdown navbar. --}}
@php
$learningTabs = [
    ['label' => 'Materi', 'route' => 'teacher.learning.materials.index', 'active' => 'teacher.learning.materials.*'],
    ['label' => 'Tugas', 'route' => 'teacher.learning.assignments.index', 'active' => 'teacher.learning.assignments.*'],
    ['label' => 'Pengumpulan', 'route' => 'teacher.learning.submissions.index', 'active' => 'teacher.learning.submissions.*'],
    ['label' => 'Penilaian', 'route' => 'teacher.learning.grades.index', 'active' => 'teacher.learning.grades.*'],
];
@endphp
<nav class="admin-view-switch" aria-label="Bagian Pembelajaran" style="margin-bottom: 1.25rem; flex-wrap: wrap;">
    @foreach ($learningTabs as $tab)
        @php $isActiveTab = request()->routeIs($tab['active']); @endphp
        <a href="{{ route($tab['route']) }}" class="{{ $isActiveTab ? 'is-active' : '' }}" @if ($isActiveTab) aria-current="page" @endif>{{ $tab['label'] }}</a>
    @endforeach
</nav>
