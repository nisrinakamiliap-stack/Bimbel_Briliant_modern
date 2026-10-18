{{-- Layout Portal Guru: navbar horizontal tanpa sidebar. Komponen formulir/tabel memakai admin.css. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Portal Guru') | {{ $institution['name'] }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @php
    $manifestPath = public_path('build/manifest.json');
    $isTeacherAssetBuilt = file_exists(public_path('hot'))
        || (file_exists($manifestPath) && str_contains((string) file_get_contents($manifestPath), 'resources/css/teacher.css'));
    @endphp

    @if ($isTeacherAssetBuilt)
        @vite(['resources/css/admin.css', 'resources/css/teacher.css', 'resources/js/admin.js', 'resources/js/teacher.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/admin.css')) !!}{!! file_get_contents(resource_path('css/teacher.css')) !!}</style>
    @endif
    @stack('styles')
</head>
<body class="teacher-body">
<a href="#teacher-content" class="admin-skip-link">Lewati ke konten utama</a>

@include('teacher.partials.navbar')

<main id="teacher-content" class="teacher-main teacher-container" tabindex="-1">
    {{-- Halaman bersama (Pesan, Notifikasi) memakai judul h2; judul utamanya diberikan di sini. --}}
    @hasSection('page_title')
        <h1 class="sr-only">@yield('page_title')</h1>
    @endif

    @yield('content')
</main>

@include('teacher.partials.message-button')

<dialog id="admin-confirm-dialog" class="admin-dialog" aria-labelledby="admin-confirm-title" aria-describedby="admin-confirm-message">
    <div class="admin-dialog-body">
        <h2 id="admin-confirm-title">Konfirmasi</h2>
        <p id="admin-confirm-message">Apakah Anda yakin?</p>
    </div>
    <div class="admin-dialog-actions">
        <button type="button" id="admin-confirm-cancel" class="admin-btn admin-btn-secondary">Batal</button>
        <button type="button" id="admin-confirm-submit" class="admin-btn admin-btn-danger">Ya, Lanjutkan</button>
    </div>
</dialog>

@unless ($isTeacherAssetBuilt)
    <script>{!! file_get_contents(resource_path('js/admin.js')) !!}{!! file_get_contents(resource_path('js/teacher.js')) !!}</script>
@endunless
@stack('scripts')
</body>
</html>
