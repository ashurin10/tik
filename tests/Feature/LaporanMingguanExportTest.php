<?php

namespace Tests\Feature;

use App\Models\LaporanMingguan;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanMingguanExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_keeps_rows_in_one_continuous_table_and_signature_in_its_own_block(): void
    {
        $user = User::factory()->create();
        for ($i = 1; $i <= 46; $i++) {
            LaporanMingguan::create([
                'tanggal' => $i < 45 ? '2026-10-16' : '2026-10-17',
                'nama_kegiatan' => "Kegiatan $i dengan uraian panjang dan perangkat jaringan",
                'lokasi' => 'Diskominfo',
                'hasil_deskripsi' => "Hasil kegiatan $i selesai",
                'prioritas' => 'Sedang',
                'pic' => 'Idhamartya Wulani',
                'status' => 'Selesai',
            ]);
        }

        $response = $this->actingAs($user)->get(route('laporan-mingguan.export', [
            'start_date' => '2026-10-16',
            'end_date' => '2026-10-17',
            'penandatangan' => 'Contoh Penandatangan',
            'jabatan' => 'Kepala Bidang',
        ]))->assertOk();
        $xpath = $this->xpath($response->getContent());

        $this->assertSame(1, $xpath->query('//table')->length);
        $this->assertSame(1, $xpath->query('//table/thead')->length);
        $this->assertSame(9, $xpath->query('//table/colgroup/col')->length);
        $this->assertSame(1, $xpath->query('//table/tbody')->length);
        $rows = $xpath->query('//table/tbody/tr[count(td) = 9]');
        $this->assertSame(46, $rows->length);
        foreach ($rows as $index => $row) {
            $this->assertSame((string) ($index + 1), trim($row->firstElementChild->textContent));
        }
        $this->assertSame(1, $xpath->query('//table/following-sibling::div[@class="ttd-container"]//div[@class="ttd-nama"]')->length);
        $this->assertSame('Contoh Penandatangan', trim($xpath->query('//div[@class="ttd-nama"]')->item(0)->textContent));
    }

    public function test_empty_and_short_reports_render_with_and_without_signature(): void
    {
        foreach ([0, 1, 2, 3] as $count) {
            foreach (['', 'Contoh Penandatangan'] as $signer) {
                $reports = collect();
                for ($i = 1; $i <= $count; $i++) {
                    $reports->push(new LaporanMingguan([
                        'tanggal' => '2026-10-16',
                        'nama_kegiatan' => "Kegiatan $i",
                        'lokasi' => 'Kantor',
                        'pic' => 'Alfian',
                        'status' => 'Selesai',
                        'prioritas' => 'Sedang',
                    ]));
                }
                $html = view('laporan-mingguan.cetak', [
                    'laporans' => $reports,
                    'startDate' => '2026-10-16',
                    'endDate' => '2026-10-17',
                    'penandatangan' => $signer,
                    'jabatan' => 'Kepala Bidang',
                    'pangkat' => '',
                    'tanggalTtd' => null,
                ])->render();
                $xpath = $this->xpath($html);

                $this->assertSame(1, $xpath->query('//table')->length);
                $this->assertSame($count, $xpath->query('//table/tbody/tr[count(td) = 9]')->length);
                $this->assertSame($signer === '' ? 0 : 1, $xpath->query('//div[@class="ttd-nama"]')->length);
                if ($count === 0) {
                    $this->assertStringContainsString('Tidak ada kegiatan pada periode ini.', $html);
                }
            }
        }
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }
}
