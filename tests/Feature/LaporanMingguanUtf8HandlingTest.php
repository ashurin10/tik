<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanMingguanUtf8HandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_parse_text_handles_malformed_utf8_and_returns_json(): void
    {
        $user = \App\Models\User::factory()->create(['peran' => 'user']);

        // Create an invalid UTF-8 byte sequence
        $invalid = pack('C*', [0xC3, 0x28]); // invalid UTF-8

        $text = "Nama Kegiatan : Tes kegiatan\nPIC : John Doe\n" . $invalid . "\nHasil : selesai";

        $this->actingAs($user)
            ->post(route('laporan-mingguan.parse-text'), ['text' => $text])
            ->assertStatus(200)
            ->assertJsonStructure([0 => ['tanggal', 'nama_kegiatan', 'pic', 'hasil_deskripsi']]);
    }
}
