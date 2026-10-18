{{--
    Judul, tab, dan filter periode laporan.

    @var string $title
    @var string $description
    @var string $route
    @var \Carbon\CarbonImmutable $from
    @var \Carbon\CarbonImmutable $to
    @var array<string, mixed> $filters
--}}
@php
$reportTabs = [
    ['label' => 'Murid & Pendaftaran', 'route' => 'admin.reports.students'],
    ['label' => 'Pembayaran', 'route' => 'admin.reports.payments'],
    ['label' => 'Absensi', 'route' => 'admin.reports.attendance'],
    ['label' => 'Pembelajaran', 'route' => 'admin.reports.learning'],
];
@endphp

<div class="admin-page-header">
    <div>
        <h2>{{ $title }}</h2>
        <p>{{ $description }} Periode {{ $from->translatedFormat('d F Y') }} – {{ $to->translatedFormat('d F Y') }}.</p>
    </div>
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="admin-btn admin-btn-secondary" id="btn-export-csv">
        <x-admin.icon name="reports" :size="16" /> Ekspor CSV
    </a>
</div>

<nav class="admin-view-switch" aria-label="Jenis laporan" style="margin-bottom: 1.25rem; flex-wrap: wrap;">
    @foreach ($reportTabs as $tab)
        @php $isActiveTab = request()->routeIs($tab['route']); @endphp
        <a href="{{ route($tab['route']) }}" class="{{ $isActiveTab ? 'is-active' : '' }}" @if ($isActiveTab) aria-current="page" @endif>{{ $tab['label'] }}</a>
    @endforeach
</nav>

@error('sampai')
    <div class="admin-alert is-error" role="alert"><x-admin.icon name="alert" :size="18" /><span>{{ $message }}</span></div>
@enderror

<form action="{{ route($route) }}" method="GET" class="admin-panel admin-toolbar is-stacked" style="margin-bottom: 1.5rem;" role="search">
    <div class="admin-toolbar-row">
        <div style="flex: 1; min-width: 10rem;">
            <label for="dari" class="admin-label">Dari</label>
            <input type="date" id="dari" name="dari" value="{{ $from->format('Y-m-d') }}" class="admin-input">
        </div>
        <div style="flex: 1; min-width: 10rem;">
            <label for="sampai" class="admin-label">Sampai</label>
            <input type="date" id="sampai" name="sampai" value="{{ $to->format('Y-m-d') }}" class="admin-input">
        </div>
        @yield('report_filters')
        <div style="align-self: flex-end;">
            <button type="submit" class="admin-btn admin-btn-primary">Tampilkan</button>
        </div>
    </div>
</form>
