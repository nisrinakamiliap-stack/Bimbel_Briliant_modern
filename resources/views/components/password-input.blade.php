@props([
    'name',
    'id' => null,
    'label',
    'autocomplete' => 'current-password',
    'placeholder' => '',
    'hint' => null,
    'showError' => true,
])

@php
$inputId = $id ?? $name;
$hasError = $showError && $errors->has($name);
$describedBy = collect([
    $hint ? $inputId.'-hint' : null,
    $hasError ? $inputId.'-error' : null,
])->filter()->implode(' ');
@endphp

<div>
    <label for="{{ $inputId }}" class="auth-label">{{ $label }} <span class="req" aria-hidden="true">*</span></label>
    <div class="auth-password-wrap">
        <input type="password"
               name="{{ $name }}"
               id="{{ $inputId }}"
               class="auth-input {{ $hasError ? 'is-invalid' : '' }}"
               autocomplete="{{ $autocomplete }}"
               placeholder="{{ $placeholder }}"
               required
               @if ($hasError) aria-invalid="true" @endif
               @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
        <button type="button"
                class="auth-toggle-password"
                data-toggle-password="{{ $inputId }}"
                aria-controls="{{ $inputId }}"
                aria-pressed="false"
                aria-label="Tampilkan password">
            <svg class="icon-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            <svg class="icon-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
        </button>
    </div>
    @if ($hint)
        <p id="{{ $inputId }}-hint" class="auth-hint">{{ $hint }}</p>
    @endif
    @if ($hasError)
        <p id="{{ $inputId }}-error" class="auth-error" role="alert">{{ $errors->first($name) }}</p>
    @endif
</div>

@once
@push('scripts')
<script>
(function () {
    'use strict';
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-toggle-password'));
            if (!input) return;
            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            button.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
            button.querySelector('.icon-show').style.display = isHidden ? 'none' : '';
            button.querySelector('.icon-hide').style.display = isHidden ? '' : 'none';
        });
    });
}());
</script>
@endpush
@endonce
