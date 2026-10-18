{{--
    Rekap jumlah per status untuk periode/filter yang dipilih.

    @var \Illuminate\Support\Collection<string, int> $statusCounts
    @var array<int, \BackedEnum> $statuses
--}}
<section class="admin-stats" aria-label="Rekap status kehadiran" id="attendance-summary">
    @foreach ($statuses as $statusOption)
        <article class="admin-stat-card">
            <div>
                <p class="admin-stat-label"><span class="admin-badge {{ $statusOption->badgeClass() }}">{{ $statusOption->label() }}</span></p>
                <p class="admin-stat-value" data-status="{{ $statusOption->value }}">{{ number_format($statusCounts[$statusOption->value] ?? 0, 0, ',', '.') }}</p>
                <p class="admin-stat-note">catatan pada periode ini</p>
            </div>
        </article>
    @endforeach
</section>
