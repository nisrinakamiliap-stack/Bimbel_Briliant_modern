@props([
    'label',
    'value',
    'icon',
    'tone' => 'green',
    'note' => null,
])

<article {{ $attributes->merge(['class' => 'admin-stat-card']) }}>
    <div class="admin-stat-icon is-{{ $tone }}">
        <x-admin.icon :name="$icon" :size="22" />
    </div>
    <div>
        <p class="admin-stat-label">{{ $label }}</p>
        <p class="admin-stat-value">{{ number_format($value ?? 0, 0, ',', '.') }}</p>
        @if ($note)
            <p class="admin-stat-note">{{ $note }}</p>
        @endif
    </div>
</article>
