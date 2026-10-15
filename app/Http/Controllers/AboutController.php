<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    /**
     * Menampilkan halaman Tentang Kami.
     */
    public function index(): View
    {
        /** @var array<string, mixed> $about */
        $about = config('bimbel.tentang_kami', []);

        return view('tentang.index', compact('about'));
    }
}
