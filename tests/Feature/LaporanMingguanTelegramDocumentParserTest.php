<?php

namespace Tests\Feature;

use App\Services\LaporanMingguanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanMingguanTelegramDocumentParserTest extends TestCase
{
    use RefreshDatabase;

    public function test_parse_multiple_texts_detects_telegram_document_sender_and_keeps_old_activity_format(): void
    {
        $text = <<<TEXT
Dedi Nugraha
In reply to this message
laporan antivirus.docx
Not included, change data exporting settings to download.
59.8 KB
Terlampir laporan penggunaan antivirus april 2026
15:27
sae
In reply to this message
LAPORAN_WAZUH_OSTICKET_BULAN_APRIL 2026.docx
Not included, change data exporting settings to download.
3.1 MB
terlampir laporan wazuh april 2026
TEXT;

        $results = app(LaporanMingguanService::class)->parseMultipleTexts($text);

        $this->assertCount(2, $results);

        $this->assertSame('Dedi Nugraha', $results[0]['pic']);
        $this->assertSame('Menyampaikan surat/dokumen terkait (laporan antivirus)', $results[0]['nama_kegiatan']);
        $this->assertSame('Terlampir laporan penggunaan antivirus april 2026', $results[0]['hasil_deskripsi']);

        $this->assertSame('sae', $results[1]['pic']);
        $this->assertSame('Menyampaikan surat/dokumen terkait (LAPORAN_WAZUH_OSTICKET_BULAN_APRIL 2026)', $results[1]['nama_kegiatan']);
        $this->assertSame('terlampir laporan wazuh april 2026', $results[1]['hasil_deskripsi']);
    }

    public function test_parse_document_without_sender_still_returns_readable_pic(): void
    {
        $text = <<<TEXT
LAPORAN_WAZUH_OSTICKET_BULAN_APRIL 2026.docx
Not included, change data exporting settings to download.
3.1 MB
terlampir laporan wazuh april 2026.
TEXT;

        $results = app(LaporanMingguanService::class)->parseMultipleTexts($text);

        $this->assertCount(1, $results);
        $this->assertSame('Belum diketahui', $results[0]['pic']);
        $this->assertSame('Menyampaikan surat/dokumen terkait (LAPORAN_WAZUH_OSTICKET_BULAN_APRIL 2026)', $results[0]['nama_kegiatan']);
        $this->assertSame('terlampir laporan wazuh april 2026.', $results[0]['hasil_deskripsi']);
    }

    public function test_parse_mixed_structured_report_and_document_attachment(): void
    {
        $text = <<<TEXT
Tanggal : 05 Mei 2026
Nama Kegiatan : Sinkronisasi backup server 1 to Pro Data Center Jakarta
Lokasi : Diskominfo Subang
Nama Pelaksana : Tio
Keterangan : Report Server
Hasil Kegiatan : Sinkronisasi backup server to Pro Data Center Jakarta selesai dilakukan
Kendala : -
LAPORAN_WAZUH_OSTICKET_BULAN_APRIL 2026.docx
Not included, change data exporting settings to download.
3.1 MB
terlampir laporan wazuh april 2026.
TEXT;

        $results = app(LaporanMingguanService::class)->parseMultipleTexts($text);

        $this->assertCount(2, $results);
        $this->assertSame('Tio', $results[0]['pic']);
        $this->assertSame('Menyampaikan surat/dokumen terkait (LAPORAN_WAZUH_OSTICKET_BULAN_APRIL 2026)', $results[1]['nama_kegiatan']);
        $this->assertSame('Belum diketahui', $results[1]['pic']);
    }
}
