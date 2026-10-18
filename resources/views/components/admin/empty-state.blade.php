@props([
    'title',
    'icon' => 'inbox',
])

<div {{ $attributes->merge(['class' => 'admin-empty']) }} role="status">
    <div class="admin-empty-icon">
        <x-admin.icon :name="$icon" :size="22" />
    </div>
    <h4>{{ $title }}</h4>
    <p>{{ $slot }}</p>
</div>
