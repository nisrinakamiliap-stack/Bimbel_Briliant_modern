<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\ProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Profil Saya untuk guru yang sedang login.
 */
class ProfileController extends Controller
{
    /**
     * Menampilkan profil beserta form field yang dapat diubah.
     */
    public function edit(Request $request): View
    {
        return view('teacher.profile.edit', [
            'teacher' => $request->user()->load('teacherProfile'),
        ]);
    }

    /**
     * Menyimpan nomor telepon, alamat, dan bio. Field lain tidak dapat diubah dari sini.
     *
     * Alamat dan bio hanya disimpan jika profil guru sudah dibuat admin (bidang ajar wajib diisi admin).
     */
    public function update(ProfileRequest $request): RedirectResponse
    {
        $teacher = $request->user();

        DB::transaction(function () use ($teacher, $request): void {
            $teacher->update(['phone' => $request->validated('phone')]);
            $teacher->teacherProfile?->update([
                'address' => $request->validated('address'),
                'bio' => $request->validated('bio'),
            ]);
        });

        return redirect()->route('teacher.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
