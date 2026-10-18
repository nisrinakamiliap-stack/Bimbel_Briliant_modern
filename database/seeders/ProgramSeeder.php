<?php

namespace Database\Seeders;

use App\Models\Program;
use App\ProgramLevel;
use Illuminate\Database\Seeder;

/**
 * Mengimpor katalog program dari config/bimbel.php ke tabel programs.
 *
 * Aman dijalankan berulang kali: program dengan slug yang sudah ada tidak diubah,
 * sehingga perubahan yang dilakukan admin tidak tertimpa.
 */
class ProgramSeeder extends Seeder
{
    /**
     * Pemetaan slug program di config ke jenjang program.
     *
     * @var array<string, ProgramLevel>
     */
    private const LEVEL_BY_SLUG = [
        'sd' => ProgramLevel::Sd,
        'smp' => ProgramLevel::Smp,
        'sma' => ProgramLevel::Sma,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var array<string, array<string, mixed>> $programs */
        $programs = config('bimbel.programs', []);

        foreach ($programs as $key => $data) {
            $slug = (string) ($data['slug'] ?? $key);

            if (Program::where('slug', $slug)->exists()) {
                continue;
            }

            $program = new Program([
                'name' => (string) $data['judul'],
                'level' => self::LEVEL_BY_SLUG[$slug] ?? ProgramLevel::Umum,
                'description' => $data['deskripsi'] ?? null,
                'price' => $this->parsePrice((string) ($data['biaya'] ?? '')),
                'duration_months' => null,
                'is_active' => true,
            ]);
            $program->slug = $slug;
            $program->save();
        }
    }

    /**
     * Mengambil angka harga dari teks seperti "Mulai dari Rp350.000 / bulan".
     */
    private function parsePrice(string $priceText): int
    {
        preg_match('/Rp\s?([\d.]+)/i', $priceText, $matches);

        return (int) str_replace('.', '', $matches[1] ?? '0');
    }
}
