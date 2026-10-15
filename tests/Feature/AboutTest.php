<?php

namespace Tests\Feature;

use Tests\TestCase;

class AboutTest extends TestCase
{
    /**
     * Test halaman tentang kami dapat diakses dan menampilkan komponen utama.
     */
    public function test_tentang_kami_page_is_accessible(): void
    {
        $response = $this->get('/tentang-kami');

        $response->assertStatus(200);
        $response->assertSee('Tentang Kami');
        $response->assertSee('Membimbing Generasi Cerdas dan Berkarakter');
        $response->assertSee('Visi Kami');
        $response->assertSee('Misi Kami');
        $response->assertSee('Nilai-Nilai Utama Kami');
        $response->assertSee('Tutor Berpengalaman &amp; Berdedikasi', false);
        $response->assertSee('Fasilitas Belajar Modern');
    }

    /**
     * Test data statistik dan angka utama ditampilkan di halaman Tentang Kami.
     */
    public function test_tentang_kami_displays_statistics(): void
    {
        $response = $this->get('/tentang-kami');

        $response->assertStatus(200);
        $response->assertSee('1.500+');
        $response->assertSee('Siswa Terbimbing');
        $response->assertSee('94.8%');
        $response->assertSee('Lolos Kampus Impian');
        $response->assertSee('45+');
        $response->assertSee('Tutor Berpengalaman');
    }

    /**
     * Test data tim tutor ditampilkan dengan baik.
     */
    public function test_tentang_kami_displays_tutor_team(): void
    {
        $response = $this->get('/tentang-kami');

        $response->assertStatus(200);
        $response->assertSee('Sari Wahyuni, M.Pd.');
        $response->assertSee('Budi Santoso, S.Si.');
        $response->assertSee('Dimas Prasetyo, S.T.');
        $response->assertSee('Ratna Kusuma, S.Pd.');
    }

    /**
     * Test halaman beranda memiliki link yang mengarah ke route tentang.index.
     */
    public function test_home_page_contains_tentang_kami_link(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('tentang.index'), false);
    }
}
