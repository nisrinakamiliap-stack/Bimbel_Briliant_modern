@props([
    'title',
    'description',
    'features' => [],
])

@php
$user = auth()->user();
@endphp

@push('styles')
    @include('auth.partials.styles')
@endpush

<section class="auth-section">
    <div class="container-main">

        @if (session('success'))
            <div class="auth-alert auth-alert--success" role="status">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="auth-card">
            <div class="auth-row" style="align-items:flex-start;margin-bottom:1.5rem;">
                <div>
                    <span class="badge-pill" style="display:inline-block;background-color:#dcfce7;color:#16845B;font-size:.75rem;font-weight:700;padding:.25rem .75rem;border-radius:999px;margin-bottom:.75rem;">
                        {{ $user->role->label() }}
                    </span>
                    <h1>{{ $title }}</h1>
                    <p class="auth-subtitle" style="margin-bottom:0;">Halo, <strong style="color:#142B4A;">{{ $user->name }}</strong>. {{ $description }}</p>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="auth-btn auth-btn--navy auth-btn--auto" id="btn-logout" style="padding:.625rem 1.25rem;font-size:.875rem;">
                        Keluar
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </button>
                </form>
            </div>

            <div style="border:1px dashed #cbd5e1;border-radius:.875rem;padding:1.5rem;background-color:#F8FAFC;">
                <p style="font-size:.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin:0 0 1rem;">
                    Halaman sementara — fitur yang akan tersedia
                </p>
                <ul style="list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem;">
                    @foreach ($features as $feature)
                        <li style="display:flex;align-items:center;gap:.625rem;font-size:.9375rem;color:#253247;background-color:#ffffff;border:1px solid #e2e8f0;border-radius:.625rem;padding:.75rem 1rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16845B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
