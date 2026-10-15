<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Menampilkan halaman Kontak & Konsultasi.
     */
    public function index(): View
    {
        /** @var array<string, mixed> $kontak */
        $kontak = config('bimbel.kontak', []);

        /** @var array<string, array<string, mixed>> $programs */
        $programs = config('bimbel.programs', []);

        return view('kontak.index', compact('kontak', 'programs'));
    }

    /**
     * Menangani pengiriman pesan dan konsultasi dari pengunjung.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'    => ['required', 'string', 'max:100'],
            'telepon' => ['required', 'string', 'max:25'],
            'email'   => ['nullable', 'email', 'max:100'],
            'jenjang' => ['required', 'string', 'max:50'],
            'pesan'   => ['required', 'string', 'max:1000'],
        ], [
            'nama.required'    => 'Mohon masukkan nama lengkap Anda.',
            'telepon.required' => 'Nomor WhatsApp atau telepon wajib diisi untuk konfirmasi jadwal.',
            'email.email'      => 'Format alamat email tidak valid.',
            'jenjang.required' => 'Silakan pilih program atau jenjang yang Anda minati.',
            'pesan.required'   => 'Mohon tuliskan pesan atau pertanyaan Anda.',
        ]);

        return redirect()->route('kontak.index')->with(
            'success',
            'Terima kasih, ' . e($validated['nama']) . '! Pesan Anda telah kami terima. Tim konselor kami akan segera menghubungi Anda melalui WhatsApp/Telepon dalam 1x24 jam kerja.'
        );
    }
}
