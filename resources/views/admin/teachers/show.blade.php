@extends('layouts.admin')

@section('title', $teacher->name.' — Data Guru')
@section('page_title', 'Data Guru')

@php
$profile = $teacher->teacherProfile;
$initials = collect(explode(' ', trim($teacher->name)))
    ->filter()
    ->take(2)
    ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
    ->implode('');
@endphp

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.teachers.index') }}">Data Guru</a></li>
                <li aria-current="page">{{ $teacher->name }}</li>
            </ol>
            <h2>Detail Guru</h2>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.teachers.index') }}" class="admin-btn admin-btn-secondary">
                <x-admin.icon name="arrow-left" :size="16" />
                Kembali
            </a>
            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="admin-btn admin-btn-primary">
                <x-admin.icon name="edit" :size="16" />
                Edit
            </a>
        </div>
    </div>

    <x-admin.flash />

    <div class="admin-grid">
        <section class="admin-panel" aria-labelledby="account-heading">
            <div class="admin-profile-card">
                <span class="admin-avatar" aria-hidden="true">{{ $initials }}</span>
                <div>
                    <h3 id="account-heading">{{ $teacher->name }}</h3>
                    <p>{{ $teacher->role->label() }} · bergabung {{ $teacher->created_at?->translatedFormat('d F Y') }}</p>
                </div>
            </div>
            <dl class="admin-detail-list">
                <div>
                    <dt>Email</dt>
                    <dd>{{ $teacher->email }}</dd>
                </div>
                <div>
                    <dt>No. Telepon / WhatsApp</dt>
                    <dd>{{ $teacher->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Tanggal Bergabung</dt>
                    <dd>{{ $teacher->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Terakhir Diperbarui</dt>
                    <dd>{{ $teacher->updated_at?->translatedFormat('d F Y, H:i') ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="admin-panel" aria-labelledby="profile-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="profile-heading">Profil Pengajar</h3>
                    <p>Bidang keahlian dan latar belakang profesional.</p>
                </div>
            </div>

            @if ($profile === null)
                <x-admin.empty-state title="Profil belum dilengkapi" icon="teachers">
                    Guru ini belum memiliki data profil pengajar. Lengkapi melalui tombol Edit.
                </x-admin.empty-state>
            @else
                <dl class="admin-detail-list">
                    <div>
                        <dt>Bidang Keahlian</dt>
                        <dd>{{ $profile->subject }}</dd>
                    </div>
                    <div>
                        <dt>Pendidikan Terakhir</dt>
                        <dd>{{ $profile->last_education?->label() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Institusi / Kampus Asal</dt>
                        <dd>{{ $profile->institution ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Alamat</dt>
                        <dd>{{ $profile->address ?? '—' }}</dd>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <dt>Profil Singkat</dt>
                        <dd>{{ $profile->bio ?? '—' }}</dd>
                    </div>
                </dl>
            @endif
        </section>

        <section class="admin-panel is-wide" aria-labelledby="teaching-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="teaching-heading">Kelas yang Diampu</h3>
                    <p>Kelas yang menetapkan guru ini sebagai pengajar.</p>
                </div>
            </div>
            @if ($teacher->taughtClasses->isEmpty())
                <x-admin.empty-state title="Belum mengampu kelas" icon="classes">
                    Tetapkan guru ini sebagai pengajar melalui menu Kelas.
                </x-admin.empty-state>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Kelas</th>
                                <th scope="col">Program</th>
                                <th scope="col">Kapasitas</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teacher->taughtClasses as $taughtClass)
                                <tr>
                                    <td><a href="{{ route('admin.classes.show', $taughtClass) }}" class="admin-cell-name" style="text-decoration: none;">{{ $taughtClass->name }}</a></td>
                                    <td>{{ $taughtClass->program->name }}</td>
                                    <td>{{ $taughtClass->capacity }} murid</td>
                                    <td>
                                        <span class="admin-badge {{ $taughtClass->is_active ? 'is-success' : 'is-neutral' }}">{{ $taughtClass->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-panel is-wide" aria-labelledby="danger-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="danger-heading">Hapus Guru</h3>
                    <p>Akun akan dinonaktifkan dan disembunyikan dari daftar. Data tetap tersimpan sebagai arsip.</p>
                </div>
                <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST"
                      data-confirm="Hapus guru {{ $teacher->name }}? Akun tidak dapat digunakan untuk login lagi, tetapi datanya tetap tersimpan sebagai arsip.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-btn admin-btn-danger-outline" id="btn-delete-teacher">
                        <x-admin.icon name="trash" :size="16" />
                        Hapus Guru
                    </button>
                </form>
            </div>
        </section>
    </div>
@endsection
