<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * Menampilkan halaman registrasi calon murid.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Mendaftarkan akun murid baru.
     *
     * Role selalu ditetapkan sebagai murid di server dan tidak pernah diambil dari input pengguna.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'email', 'phone', 'password']));
        $user->role = UserRole::Student;
        $user->save();

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('student.dashboard')->with(
            'success',
            'Registrasi berhasil! Selamat datang di Bimbel Modern Brilliant, '.$user->name.'.'
        );
    }
}
