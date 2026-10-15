<?php

namespace Tests\Feature;

use Tests\TestCase;

class JadwalTest extends TestCase
{
    /**
     * Test halaman jadwal dapat diakses dan menampilkan komponen utama.
     */
    public function test_jadwal_page_is_accessible(): void
    {
        $response = $this->get('/jadwal');

        $response->assertStatus(200);
        $response->assertSee('Jadwal Pembelajaran');
        $response->assertSee('Lihat semua kelas yang tersedia dan pilih waktu yang sesuai');
        $response->assertSee('Semua');
        $response->assertSee('SD');
        $response->assertSee('SMP');
        $response->assertSee('SMA');
        $response->assertSee('UTBK');
        $response->assertSee('Minggu Lalu');
        $response->assertSee('Minggu Depan');
    }

    /**
     * Test filter jenjang SD hanya menampilkan kelas SD.
     */
    public function test_jadwal_filter_sd(): void
    {
        $response = $this->get('/jadwal?jenjang=sd');

        $response->assertStatus(200);
        $response->assertSee('Matematika SD');
        $response->assertSee('Bu Sari');
        $response->assertDontSee('Fisika SMA');
    }

    /**
     * Test filter jenjang SMA hanya menampilkan kelas SMA.
     */
    public function test_jadwal_filter_sma(): void
    {
        $response = $this->get('/jadwal?jenjang=sma');

        $response->assertStatus(200);
        $response->assertSee('Matematika SMA');
        $response->assertSee('Pak Hendra');
        $response->assertDontSee('Matematika SD');
    }

    /**
     * Test filter jenjang UTBK menampilkan kelas persiapan ujian.
     */
    public function test_jadwal_filter_utbk(): void
    {
        $response = $this->get('/jadwal?jenjang=utbk');

        $response->assertStatus(200);
        $response->assertSee('Penalaran Kuantitatif UTBK');
        $response->assertSee('Kak Dimas');
    }

    /**
     * Test navigasi minggu mengganti parameter dan data minggu.
     */
    public function test_jadwal_week_navigation(): void
    {
        $responseNext = $this->get('/jadwal?week=1');
        $responseNext->assertStatus(200);
        $responseNext->assertSee('Kembali ke Minggu Ini');

        $responsePrev = $this->get('/jadwal?week=-1');
        $responsePrev->assertStatus(200);
        $responsePrev->assertSee('Kembali ke Minggu Ini');
    }

    /**
     * Test pesan kosong yang ramah ketika tidak ada jadwal di minggu tertentu.
     */
    public function test_jadwal_empty_state_on_far_week(): void
    {
        $response = $this->get('/jadwal?week=10');

        $response->assertStatus(200);
        $response->assertSee('Tidak Ada Jadwal Kelas Ditemukan');
        $response->assertSee('Lihat Semua Jadwal Minggu Ini');
    }

    /**
     * Test kartu jadwal mengarah ke halaman detail program yang sesuai.
     */
    public function test_jadwal_items_link_to_program_show(): void
    {
        $response = $this->get('/jadwal');

        $response->assertStatus(200);
        $response->assertSee(route('program.show', 'sd'), false);
        $response->assertSee(route('program.show', 'smp'), false);
        $response->assertSee(route('program.show', 'sma'), false);
        $response->assertSee(route('program.show', 'persiapan-ujian'), false);
    }
}
