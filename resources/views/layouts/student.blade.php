{{-- Layout Portal Murid: navbar horizontal tanpa sidebar. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Portal Murid') | {{ $institution['name'] }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @php
    $manifestPath = public_path('build/manifest.json');
    $isStudentAssetBuilt = file_exists(public_path('hot'))
        || (file_exists($manifestPath) && str_contains((string) file_get_contents($manifestPath), 'resources/css/student.css'));
    @endphp

    @if ($isStudentAssetBuilt)
        @vite(['resources/css/admin.css', 'resources/css/student.css', 'resources/js/student.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/admin.css')) !!}{!! file_get_contents(resource_path('css/student.css')) !!}</style>
    @endif

    @stack('styles')
</head>
<body class="student-body">
<a href="#st-content" class="st-skip-link">Lewati ke konten utama</a>

@include('student.partials.navbar')

<main id="st-content" class="st-main" tabindex="-1">
    @yield('content')
</main>

@include('student.partials.message-button')

@unless ($isStudentAssetBuilt)
    <script>{!! file_get_contents(resource_path('js/student.js')) !!}</script>
@endunless
@stack('scripts')
</body>
</html>
