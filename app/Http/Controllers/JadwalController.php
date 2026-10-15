<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Menampilkan halaman jadwal pembelajaran.
     */
    public function index(Request $request): View
    {
        $jenjangAktif = strtolower((string) $request->query('jenjang', 'semua'));
        $validJenjang = ['semua', 'sd', 'smp', 'sma', 'utbk'];
        if (! in_array($jenjangAktif, $validJenjang, true)) {
            $jenjangAktif = 'semua';
        }

        $weekOffset = (int) $request->query('week', 0);

        // Inisialisasi tanggal dengan locale Indonesia
        $baseDate = Carbon::now()->locale('id');
        $startOfWeek = $baseDate->copy()->addWeeks($weekOffset)->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $startOfWeek->copy()->addDays(5); // Senin s/d Sabtu

        // Format label rentang tanggal (contoh: "12 - 17 Oktober 2026")
        if ($startOfWeek->year !== $endOfWeek->year) {
            $rentangTanggal = $startOfWeek->translatedFormat('j M Y') . ' - ' . $endOfWeek->translatedFormat('j M Y');
        } elseif ($startOfWeek->month !== $endOfWeek->month) {
            $rentangTanggal = $startOfWeek->translatedFormat('j M') . ' - ' . $endOfWeek->translatedFormat('j M Y');
        } else {
            $rentangTanggal = $startOfWeek->translatedFormat('j') . ' - ' . $endOfWeek->translatedFormat('j F Y');
        }

        /** @var array<int, array<string, mixed>> $templates */
        $templates = config('bimbel.jadwal', []);

        // Untuk mensimulasikan minggu tanpa jadwal (contoh: jika user berpindah lebih dari 2 minggu ke depan/belakang)
        $isSimulatedEmptyWeek = abs($weekOffset) > 2;

        $jadwalList = [];

        if (! $isSimulatedEmptyWeek) {
            foreach ($templates as $item) {
                // Filter jenjang jika dipilih spesifik
                if ($jenjangAktif !== 'semua' && ($item['jenjang'] ?? '') !== $jenjangAktif) {
                    continue;
                }

                $hariKe = (int) ($item['hari_ke'] ?? 1);
                $dayOffset = max(0, min(5, $hariKe - 1));
                $itemDate = $startOfWeek->copy()->addDays($dayOffset);

                $jadwal = $item;
                $jadwal['tanggal'] = $itemDate->format('Y-m-d');
                $jadwal['nama_hari'] = $itemDate->translatedFormat('l');
                $jadwal['nama_hari_singkat'] = $itemDate->translatedFormat('D');
                $jadwal['tanggal_singkat'] = $itemDate->translatedFormat('j M');

                $jadwalList[] = $jadwal;
            }

            // Urutkan jadwal berdasarkan tanggal lalu jam_mulai
            usort($jadwalList, function ($a, $b) {
                if ($a['tanggal'] === $b['tanggal']) {
                    return strcmp($a['jam_mulai'], $b['jam_mulai']);
                }

                return strcmp($a['tanggal'], $b['tanggal']);
            });
        }

        $prevWeek = $weekOffset - 1;
        $nextWeek = $weekOffset + 1;
        $totalJadwal = count($jadwalList);

        return view('jadwal.index', compact(
            'jadwalList',
            'jenjangAktif',
            'rentangTanggal',
            'weekOffset',
            'prevWeek',
            'nextWeek',
            'totalJadwal'
        ));
    }
}
