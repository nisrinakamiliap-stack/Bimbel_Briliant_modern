<?php

namespace Tests\Feature;

use Tests\TestCase;

class ContactTest extends TestCase
{
    /**
     * Test halaman kontak dapat diakses dan menampilkan komponen utama.
     */
    public function test_contact_page_is_accessible(): void
    {
        $response = $this->get('/kontak');

        $response->assertStatus(200);
        $response->assertSee('Kami Siap Membantu Perjalanan Belajarmu');
        $response->assertSee('WhatsApp Konsultasi Belajar');
        $response->assertSee('0812-3456-7890');
        $response->assertSee('Ajukan Konsultasi / Kirim Pesan');
        $response->assertSee('Pertanyaan yang Sering Diajukan');
    }

    /**
     * Test pengiriman formulir kontak dengan data valid berhasil.
     */
    public function test_contact_form_submits_successfully(): void
    {
        $payload = [
            'nama'    => 'Ahmad Zaki',
            'telepon' => '081298765432',
            'email'   => 'zaki@example.com',
            'jenjang' => 'SMA',
            'pesan'   => 'Saya ingin bertanya tentang program intensif persiapan SNBT UTBK.',
        ];

        $response = $this->post('/kontak', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Mengikuti redirect untuk memastikan flash message tampil di halaman
        $followRedirect = $this->followRedirects($response);
        $followRedirect->assertSee('Pesan Terkirim Berhasil!');
        $followRedirect->assertSee('Ahmad Zaki');
    }

    /**
     * Test validasi formulir kontak saat data kosong.
     */
    public function test_contact_form_validation_errors(): void
    {
        $response = $this->post('/kontak', []);

        $response->assertSessionHasErrors(['nama', 'telepon', 'jenjang', 'pesan']);
    }

    /**
     * Test halaman beranda memiliki link yang mengarah ke route kontak.index.
     */
    public function test_home_page_contains_kontak_link(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('kontak.index'), false);
    }
}
