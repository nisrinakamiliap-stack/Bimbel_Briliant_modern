<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProgramTest extends TestCase
{
    /**
     * Test halaman beranda menampilkan link yang tepat ke program.
     */
    public function test_home_page_contains_program_links(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('program.index'), false);
        $response->assertSee('Jelajahi Program');
        $response->assertSee('Program Belajar');
        $response->assertSee('Program Tersedia');
    }

    /**
     * Test halaman index program menampilkan 4 program dan badge sesuai.
     */
    public function test_program_index_page_displays_all_four_programs(): void
    {
        $response = $this->get('/program');

        $response->assertStatus(200);
        $response->assertSee('Program Belajar');
        $response->assertSee('Pilih program sesuai kebutuhan dan target belajarmu');

        // Memastikan 4 judul program ada
        $response->assertSee('Bimbingan Belajar SD');
        $response->assertSee('Bimbingan Belajar SMP');
        $response->assertSee('Bimbingan Belajar SMA');
        $response->assertSee('Persiapan Ujian');

        // Memastikan badge kelas dan populer ada
        $response->assertSee('Kelas 1-6');
        $response->assertSee('Kelas 7-9');
        $response->assertSee('Kelas 10-12');
        $response->assertSee('Populer');

        // Memastikan link detail program ada
        $response->assertSee(route('program.show', 'sd'), false);
        $response->assertSee(route('program.show', 'smp'), false);
        $response->assertSee(route('program.show', 'sma'), false);
        $response->assertSee(route('program.show', 'persiapan-ujian'), false);
    }

    /**
     * Test halaman detail program SD menampilkan data yang sesuai.
     */
    public function test_program_detail_sd_displays_correct_data(): void
    {
        $response = $this->get('/program/sd');

        $response->assertStatus(200);
        $response->assertSee('Bimbingan Belajar SD');
        $response->assertSee('Kelas 1-6');
        $response->assertSee('Materi yang Diajarkan');
        $response->assertSee('Jadwal Belajar');
        $response->assertSee('Manfaat &amp; Keunggulan', false);
        $response->assertSee('Daftar Sekarang');
        $response->assertSee('Konsultasi Gratis');
        $response->assertSee('Beranda');
        $response->assertSee('Program Belajar');
    }

    /**
     * Test halaman detail program Persiapan Ujian menampilkan badge Populer.
     */
    public function test_program_detail_persiapan_ujian_displays_popular_badge(): void
    {
        $response = $this->get('/program/persiapan-ujian');

        $response->assertStatus(200);
        $response->assertSee('Persiapan Ujian');
        $response->assertSee('Program Populer');
        $response->assertSee('Daftar Sekarang');
        $response->assertSee('Konsultasi Gratis');
    }

    /**
     * Test alias slug ujian juga dapat diakses.
     */
    public function test_program_detail_alias_ujian_works(): void
    {
        $response = $this->get('/program/ujian');

        $response->assertStatus(200);
        $response->assertSee('Persiapan Ujian');
    }

    /**
     * Test slug yang tidak valid mengembalikan 404.
     */
    public function test_invalid_program_slug_returns_404(): void
    {
        $response = $this->get('/program/non-existent-program-slug-123');

        $response->assertStatus(404);
    }
}
