<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Menampilkan daftar seluruh program belajar.
     */
    public function index(): View
    {
        /** @var array<string, array<string, mixed>> $programs */
        $programs = config('bimbel.programs', []);

        return view('programs.index', compact('programs'));
    }

    /**
     * Menampilkan halaman detail program berdasarkan slug.
     */
    public function show(string $slug): View
    {
        /** @var array<string, array<string, mixed>> $programs */
        $programs = config('bimbel.programs', []);

        $program = null;

        // Cari berdasarkan key atau field slug atau aliases
        if (isset($programs[$slug])) {
            $program = $programs[$slug];
        } else {
            foreach ($programs as $item) {
                if (($item['slug'] ?? null) === $slug) {
                    $program = $item;
                    break;
                }

                if (! empty($item['aliases']) && in_array($slug, (array) $item['aliases'], true)) {
                    $program = $item;
                    break;
                }
            }
        }

        if (! $program) {
            abort(404, 'Program belajar tidak ditemukan.');
        }

        return view('programs.show', compact('program'));
    }
}
