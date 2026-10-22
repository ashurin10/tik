<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LaporanMingguanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanMingguanTelegramMediaParserTest extends TestCase
{
    use RefreshDatabase;

    private const PHOTO_EXPORT = <<<'TEXT'
-
13 October 2026
10:02
Idhamartya Wulani
In reply to this message
Photo
Not included, change data exporting settings to download.
1012×475, 133.5 KB.
TEXT;

    public function test_photo_metadata_alone_does_not_create_an_activity(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('laporan-mingguan.parse-text'), ['text' => self::PHOTO_EXPORT])
            ->assertOk()
            ->assertExactJson([]);

        $this->assertDatabaseCount('laporan_mingguans', 0);
    }

    public function test_photo_export_after_a_report_does_not_pollute_obstacles_or_status(): void
    {
        $report = "Tanggal : 12 Oktober 2026\nNama Kegiatan : Memeriksa jaringan\nLokasi : Kantor\nPIC : Alfian\nHasil : Pemeriksaan selesai\nKendala : ";

        foreach (["\n", "\r\n", "\n\n"] as $separator) {
            $photo = str_replace("\n", $separator, self::PHOTO_EXPORT);
            $results = app(LaporanMingguanService::class)->parseMultipleTexts($report . $photo);

            $this->assertCount(1, $results);
            $this->assertSame('2026-10-12', $results[0]['tanggal']);
            $this->assertSame('Memeriksa jaringan', $results[0]['nama_kegiatan']);
            $this->assertSame('Alfian', $results[0]['pic']);
            $this->assertSame('Pemeriksaan selesai', $results[0]['hasil_deskripsi']);
            $this->assertSame('Selesai', $results[0]['status']);
            $this->assertStringNotContainsString('Idhamartya', $results[0]['keterangan_tindak_lanjut']);
            $this->assertStringNotContainsString('Photo', $results[0]['keterangan_tindak_lanjut']);
        }
    }

    public function test_metadata_is_removed_from_every_structured_field(): void
    {
        foreach (['Nama Kegiatan', 'Lokasi', 'Nama Pelaksana', 'Hasil Kegiatan', 'Kendala'] as $field) {
            $text = "Tanggal : 12 Oktober 2026\nNama Kegiatan : Memeriksa jaringan\nLokasi : Kantor\nNama Pelaksana : Alfian\nHasil Kegiatan : Pemeriksaan selesai\nKendala : Menunggu penggantian kabel";
            $value = [
                'Nama Kegiatan' => 'Memeriksa jaringan',
                'Lokasi' => 'Kantor',
                'Nama Pelaksana' => 'Alfian',
                'Hasil Kegiatan' => 'Pemeriksaan selesai',
                'Kendala' => 'Menunggu penggantian kabel',
            ][$field];
            $text = str_replace("$field : $value", "$field : $value\n" . self::PHOTO_EXPORT, $text);

            $result = app(LaporanMingguanService::class)->parseText($text);

            $this->assertSame('Memeriksa jaringan', $result['nama_kegiatan']);
            $this->assertSame('Kantor', $result['lokasi']);
            $this->assertSame('Alfian', $result['pic']);
            $this->assertSame('Pemeriksaan selesai', $result['hasil_deskripsi']);
            $this->assertSame('Menunggu penggantian kabel', $result['keterangan_tindak_lanjut']);
            $this->assertSame('Tertunda', $result['status']);
        }
    }

    public function test_meaningful_photo_caption_is_preserved_without_metadata(): void
    {
        $text = self::PHOTO_EXPORT . "\nMemeriksa jaringan kantor";

        $results = app(LaporanMingguanService::class)->parseMultipleTexts($text);

        $this->assertCount(1, $results);
        $this->assertSame('2026-10-13', $results[0]['tanggal']);
        $this->assertSame('Memeriksa jaringan kantor', $results[0]['nama_kegiatan']);
        $this->assertStringNotContainsString('Photo', $results[0]['hasil_deskripsi']);
    }

    public function test_document_attachment_after_a_photo_keeps_its_sender_and_chat_date(): void
    {
        $text = self::PHOTO_EXPORT . "\nDedi Nugraha\nIn reply to this message\nlaporan jaringan.pdf\n1.2 MB\nTerlampir laporan jaringan";

        $results = app(LaporanMingguanService::class)->parseMultipleTexts($text);

        $this->assertCount(1, $results);
        $this->assertSame('2026-10-13', $results[0]['tanggal']);
        $this->assertSame('Dedi Nugraha', $results[0]['pic']);
        $this->assertSame('Terlampir laporan jaringan', $results[0]['hasil_deskripsi']);
    }
}
