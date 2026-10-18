{{-- Navigasi tab Absensi Murid / Absensi Guru. --}}
<nav class="admin-view-switch" aria-label="Jenis absensi" style="margin-bottom: 1.25rem;">
    <a href="{{ route('admin.attendances.students.index') }}" class="{{ request()->routeIs('admin.attendances.students.*') ? 'is-active' : '' }}" @if (request()->routeIs('admin.attendances.students.*')) aria-current="page" @endif>Absensi Murid</a>
    <a href="{{ route('admin.attendances.teachers.index') }}" class="{{ request()->routeIs('admin.attendances.teachers.*') ? 'is-active' : '' }}" @if (request()->routeIs('admin.attendances.teachers.*')) aria-current="page" @endif>Absensi Guru</a>
</nav>
