<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LaporanMingguanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanMingguanStructuredReportParserTest extends TestCase
{
    use RefreshDatabase;

    private const EXAMPLE = 'Hari, Tanggal : Jumat, 16 Oktober 2026 Nama Kegiatan : Membuat desain header gform dan [s.id](https://s.id) webinar pesanmanis Lokasi : Diskominfo Nama Pelaksana : Alfian Ket. Kegiatan : - Hasil Kegiatan : Desain Header Kendala : - Foto : Terlampir.';

    public function test_parse_endpoint_extracts_each_field_from_single_line_report(): void
    {
        $user = User::factory()->create(['peran' => 'user']);

        $this->actingAs($user)
            ->postJson(route('laporan-mingguan.parse-text'), ['text' => self::EXAMPLE])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.tanggal', '2026-10-16')
            ->assertJsonPath('0.nama_kegiatan', 'Membuat desain header gform dan [s.id](https://s.id) webinar pesanmanis')
            ->assertJsonPath('0.lokasi', 'Diskominfo')
            ->assertJsonPath('0.pic', 'Alfian')
            ->assertJsonPath('0.hasil_deskripsi', 'Desain Header')
            ->assertJsonPath('0.status', 'Berjalan');
    }

    public function test_multiple_reports_with_different_date_labels_are_kept_separate(): void
    {
        $second = 'Tanggal : 17 Oktober 2026 Nama Kegiatan : Membuat poster Lokasi : Kantor Nama Pelaksana : Dedi Hasil Kegiatan : Poster selesai Kendala : -';
        $third = 'Hari/Tanggal : Minggu, 18 Oktober 2026 Nama Kegiatan : Menguji aplikasi Lokasi : Lab Nama Pelaksana : Tio Hasil Kegiatan : Pengujian selesai Kendala : -';

        foreach ([" ", "\n\n", "\r\n"] as $separator) {
            $results = app(LaporanMingguanService::class)->parseMultipleTexts(
                implode($separator, [self::EXAMPLE, $second, $third])
            );

            $this->assertCount(3, $results);
            $this->assertSame(['2026-10-16', '2026-10-17', '2026-10-18'], array_column($results, 'tanggal'));
            $this->assertSame(['Alfian', 'Dedi', 'Tio'], array_column($results, 'pic'));
            $this->assertSame('Desain Header', $results[0]['hasil_deskripsi']);
        }
    }

    public function test_multiline_fields_blank_lines_and_bullets_stay_in_one_report(): void
    {
        $text = <<<'TEXT'
**Hari, Tanggal:** Jumat, 16 Oktober 2026

*Nama Kegiatan* : Membuat desain header
gform dan s.id webinar pesanmanis
*Lokasi:* Diskominfo

Nama Pelaksana : Alfian
Ket. Kegiatan : -
Hasil Kegiatan :
- Desain header selesai
- Tautan https://s.id/pesanmanis siap

Kendala : -
Foto : Terlampir.
TEXT;

        $results = app(LaporanMingguanService::class)->parseMultipleTexts($text);

        $this->assertCount(1, $results);
        $this->assertSame('2026-10-16', $results[0]['tanggal']);
        $this->assertSame("Membuat desain header\ngform dan s.id webinar pesanmanis", $results[0]['nama_kegiatan']);
        $this->assertSame('Diskominfo', $results[0]['lokasi']);
        $this->assertSame('Alfian', $results[0]['pic']);
        $this->assertSame("- Desain header selesai\n- Tautan https://s.id/pesanmanis siap", $results[0]['hasil_deskripsi']);
        $this->assertSame('Selesai', $results[0]['status']);
    }

    public function test_empty_fields_do_not_capture_next_label_and_notes_can_be_used_as_description(): void
    {
        $text = "Tanggal : 16/10/2026\nNama Kegiatan : Membuat desain\nLokasi :\nNama Pelaksana : Alfian\nKet. Kegiatan : Menyiapkan materi webinar\nHasil Kegiatan :\nKendala :\nFoto : Terlampir.";

        $result = app(LaporanMingguanService::class)->parseText($text);

        $this->assertSame('2026-10-16', $result['tanggal']);
        $this->assertSame('', $result['lokasi']);
        $this->assertSame('Alfian', $result['pic']);
        $this->assertSame('Menyiapkan materi webinar', $result['hasil_deskripsi']);
        $this->assertSame('Berjalan', $result['status']);
        $this->assertStringNotContainsString('Terlampir', $result['keterangan_tindak_lanjut']);
    }

    public function test_iso_date_and_multiline_obstacles_are_preserved_without_photo(): void
    {
        $text = "Tanggal : 2026-10-16\nNama Kegiatan : Membuat desain\nLokasi : Kantor\nNama Pelaksana : Alfian\nHasil Kegiatan : Draf desain\nKendala : Menunggu bahan\nLogo belum diterima\nFoto : Terlampir.";

        $result = app(LaporanMingguanService::class)->parseText($text);

        $this->assertSame('2026-10-16', $result['tanggal']);
        $this->assertSame("Menunggu bahan\nLogo belum diterima", $result['keterangan_tindak_lanjut']);
        $this->assertSame('Tertunda', $result['status']);
    }

    public function test_report_without_date_supports_field_aliases_and_keeps_urls(): void
    {
        $text = 'Nama Kegiatan : Membuat halaman https://s.id/pesanmanis Tempat : Diskominfo PIC : Alfian Keterangan Kegiatan : Materi webinar Hasil : Halaman selesai Dokumentasi : Terlampir.';

        $results = app(LaporanMingguanService::class)->parseMultipleTexts($text);

        $this->assertCount(1, $results);
        $this->assertSame('Membuat halaman https://s.id/pesanmanis', $results[0]['nama_kegiatan']);
        $this->assertSame('Diskominfo', $results[0]['lokasi']);
        $this->assertSame('Alfian', $results[0]['pic']);
        $this->assertSame('Halaman selesai', $results[0]['hasil_deskripsi']);
        $this->assertSame('Selesai', $results[0]['status']);
    }

    public function test_more_than_five_reports_are_returned_in_order_without_losing_fields(): void
    {
        foreach ([6, 10, 25, 100] as $count) {
            $reports = [];
            for ($i = 1; $i <= $count; $i++) {
                $reports[] = "Hari, Tanggal : Jumat, 16 Oktober 2026 Nama Kegiatan : Membuat desain $i Lokasi : Kantor $i Nama Pelaksana : Pelaksana $i Hasil Kegiatan : Desain $i selesai Kendala : - Foto : Terlampir.";
            }

            $results = app(LaporanMingguanService::class)->parseMultipleTexts(implode("\n\n", $reports));

            $this->assertCount($count, $results);
            foreach ($results as $index => $result) {
                $i = $index + 1;
                $this->assertSame("Membuat desain $i", $result['nama_kegiatan']);
                $this->assertSame("Pelaksana $i", $result['pic']);
                $this->assertSame("Kantor $i", $result['lokasi']);
                $this->assertSame("Desain $i selesai", $result['hasil_deskripsi']);
            }
        }
    }

    public function test_ten_reports_sharing_five_date_headers_are_all_returned_by_endpoint(): void
    {
        $reports = [];
        for ($i = 1; $i <= 10; $i++) {
            $day = 11 + intdiv($i - 1, 2);
            $date = $i % 2 === 1 ? "Tanggal : $day Oktober 2026 " : '';
            $reports[] = $date . "Nama Kegiatan : Membuat desain $i Lokasi : Diskominfo Nama Pelaksana : Pelaksana $i Hasil Kegiatan : Desain $i selesai Kendala : -";
        }

        $response = $this->actingAs(User::factory()->create(['peran' => 'user']))
            ->postJson(route('laporan-mingguan.parse-text'), ['text' => implode("\n\n", $reports)])
            ->assertOk()
            ->assertJsonCount(10);

        for ($i = 1; $i <= 10; $i++) {
            $index = $i - 1;
            $day = 11 + intdiv($index, 2);
            $response->assertJsonPath("$index.nama_kegiatan", "Membuat desain $i")
                ->assertJsonPath("$index.pic", "Pelaksana $i")
                ->assertJsonPath("$index.tanggal", "2026-10-$day")
                ->assertJsonPath("$index.hasil_deskripsi", "Desain $i selesai");
        }
    }

    public function test_ten_reports_without_dates_are_split_on_activity_names(): void
    {
        $reports = [];
        for ($i = 1; $i <= 10; $i++) {
            $reports[] = "Nama Kegiatan : Membuat poster $i Lokasi : Kantor PIC : Pelaksana $i Hasil : Poster $i selesai Kendala : -";
        }

        foreach ([" ", "\n\n", "\r\n"] as $separator) {
            $results = app(LaporanMingguanService::class)->parseMultipleTexts(implode($separator, $reports));

            $this->assertCount(10, $results);
            foreach ($results as $index => $result) {
                $i = $index + 1;
                $this->assertSame("Membuat poster $i", $result['nama_kegiatan']);
                $this->assertSame("Pelaksana $i", $result['pic']);
                $this->assertSame("Poster $i selesai", $result['hasil_deskripsi']);
            }
        }
    }

    public function test_mixed_reports_with_and_without_dates_and_documents_keep_every_activity(): void
    {
        $text = <<<'TEXT'
Nama Kegiatan : Membuat poster 1
PIC : Alfian
Hasil : Poster 1 selesai
Kendala : -
Tanggal : 16 Oktober 2026
Nama Kegiatan : Membuat poster 2
PIC : Alfian
Hasil : Poster 2 selesai
Kendala : -
Nama Kegiatan : Membuat poster 3
PIC : Tio
Hasil : Poster 3 selesai
Kendala : -
Document from Dedi
laporan jaringan.pdf
1.2 MB
Terlampir laporan jaringan
Nama Kegiatan : Membuat poster 4
PIC : Tio
Hasil : Poster 4 selesai
Kendala : -
Tanggal : 17 Oktober 2026
Nama Kegiatan : Membuat poster 5
PIC : Alfian
Hasil : Poster 5 selesai
Kendala : -
Nama Kegiatan : Membuat poster 6
PIC : Alfian
Hasil : Poster 6 selesai
Kendala : -
TEXT;

        $results = app(LaporanMingguanService::class)->parseMultipleTexts($text);

        $this->assertCount(7, $results);
        $this->assertSame([
            'Membuat poster 1', 'Membuat poster 2', 'Membuat poster 3',
            'Menyampaikan surat/dokumen terkait (laporan jaringan)',
            'Membuat poster 4', 'Membuat poster 5', 'Membuat poster 6',
        ], array_column($results, 'nama_kegiatan'));
        $this->assertSame('Dedi', $results[3]['pic']);
        $this->assertSame('2026-10-16', $results[2]['tanggal']);
        $this->assertSame('2026-10-17', $results[6]['tanggal']);
    }

    public function test_day_date_report_can_be_combined_with_document_message(): void
    {
        $text = self::EXAMPLE . "\nDocument from Dedi\nlaporan jaringan.pdf\n1.2 MB\nTerlampir laporan jaringan";

        $results = app(LaporanMingguanService::class)->parseMultipleTexts($text);

        $this->assertCount(2, $results);
        $this->assertSame('Alfian', $results[0]['pic']);
        $this->assertSame('Dedi', $results[1]['pic']);
        $this->assertSame('Menyampaikan surat/dokumen terkait (laporan jaringan)', $results[1]['nama_kegiatan']);
    }
}
