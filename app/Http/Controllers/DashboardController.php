<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Mengarahkan pengguna ke dashboard sesuai role-nya.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->dashboardRoute());
    }

    /**
     * Dashboard admin (placeholder).
     */
    public function admin(): View
    {
        return view('dashboard.admin');
    }

    /**
     * Dashboard guru/tentor (placeholder).
     */
    public function teacher(): View
    {
        return view('dashboard.teacher');
    }

    /**
     * Dashboard murid (placeholder).
     */
    public function student(): View
    {
        return view('dashboard.student');
    }
}
