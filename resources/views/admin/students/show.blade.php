@extends('layouts.admin')

@section('title', $student->name.' — Data Murid')
@section('page_title', 'Data Murid')

@php
$profile = $student->studentProfile;
$initials = collect(explode(' ', trim($student->name)))
    ->filter()
    ->take(2)
    ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
    ->implode('');
@endphp

@section('content')
    <div class="admin-page-header">
        <div>
            <ol class="admin-breadcrumb">
                <li><a href="{{ route('admin.students.index') }}">Data Murid</a></li>
                <li aria-current="page">{{ $student->name }}</li>
            </ol>
            <h2>Detail Murid</h2>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.students.index') }}" class="admin-btn admin-btn-secondary">
                <x-admin.icon name="arrow-left" :size="16" />
                Kembali
            </a>
            <a href="{{ route('admin.students.edit', $student) }}" class="admin-btn admin-btn-primary">
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
                    <h3 id="account-heading">{{ $student->name }}</h3>
                    <p>{{ $student->role->label() }} · terdaftar {{ $student->created_at?->translatedFormat('d F Y') }}</p>
                </div>
            </div>
            <dl class="admin-detail-list">
                <div>
                    <dt>Email</dt>
                    <dd>{{ $student->email }}</dd>
                </div>
                <div>
                    <dt>No. Telepon / WhatsApp</dt>
                    <dd>{{ $student->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Tanggal Terdaftar</dt>
                    <dd>{{ $student->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Terakhir Diperbarui</dt>
                    <dd>{{ $student->updated_at?->translatedFormat('d F Y, H:i') ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="admin-panel" aria-labelledby="profile-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="profile-heading">Profil Murid</h3>
                    <p>Informasi pendidikan dan kontak orang tua/wali.</p>
                </div>
            </div>

            @if ($profile === null)
                <x-admin.empty-state title="Profil belum dilengkapi" icon="students">
                    Murid ini mendaftar melalui website dan belum memiliki data profil. Lengkapi melalui tombol Edit.
                </x-admin.empty-state>
            @else
                <dl class="admin-detail-list">
                    <div>
                        <dt>Jenjang Pendidikan</dt>
                        <dd>{{ $profile->education_level?->label() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Asal Sekolah</dt>
                        <dd>{{ $profile->school_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal Lahir</dt>
                        <dd>{{ $profile->birth_date?->translatedFormat('d F Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Nama Orang Tua / Wali</dt>
                        <dd>{{ $profile->parent_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Telepon Orang Tua / Wali</dt>
                        <dd>{{ $profile->parent_phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Alamat</dt>
                        <dd>{{ $profile->address ?? '—' }}</dd>
                    </div>
                </dl>
            @endif
        </section>

        <section class="admin-panel is-wide" aria-labelledby="student-enrollments-heading">
            <div class="admin-panel-header">
                <div>
                    <h3 id="student-enrollments-heading">Riwayat Pendaftaran</h3>
                    <p>Program dan kelas yang pernah atau sedang diikuti murid ini.</p>
                </div>
                <a href="{{ route('admin.enrollments.create', ['murid' => $student->id]) }}" class="admin-btn admin-btn-secondary admin-btn-sm">
                    <x-admin.icon name="plus" :size="14" /> Daftarkan ke Program
                </a>
            </div>
            @if ($student->enrollments->isEmpty())
                <x-admin.empty-state title="Belum ada pendaftaran" icon="enrollments">
                    Murid ini belum terdaftar di program mana pun.
                </x-admin.empty-state>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Program</th>
                                <th scope="col">Kelas</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($student->enrollments as $studentEnrollment)
                                <tr>
                                    <td><a href="{{ route('admin.enrollments.show', $studentEnrollment) }}" class="admin-cell-name" style="text-decoration: none;">{{ $studentEnrollment->program->name }}</a></td>
                                    <td>{{ $studentEnrollment->classroom?->name ?? 'Belum ditentukan' }}</td>
                                    <td>{{ $studentEnrollment->enrolled_on->translatedFormat('d M Y') }}</td>
                                    <td><span class="admin-badge {{ $studentEnrollment->status->badgeClass() }}">{{ $studentEnrollment->status->label() }}</span></td>
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
                    <h3 id="danger-heading">Hapus Murid</h3>
                    <p>Akun akan dinonaktifkan dan disembunyikan dari daftar. Data tetap tersimpan sebagai arsip.</p>
                </div>
                <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
                      data-confirm="Hapus murid {{ $student->name }}? Akun tidak dapat digunakan untuk login lagi, tetapi datanya tetap tersimpan sebagai arsip.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-btn admin-btn-danger-outline" id="btn-delete-student">
                        <x-admin.icon name="trash" :size="16" />
                        Hapus Murid
                    </button>
                </form>
            </div>
        </section>
    </div>
@endsection
