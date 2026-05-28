<?php

namespace Tests\Feature;

use App\Models\AsetTik;
use App\Models\AsetTikKategori;
use App\Models\AsetTikLokasi;
use App\Models\AsetTikPenanggungJawab;
use App\Models\AsetTikRiwayat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsetTikTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_asset_master_data_and_assets(): void
    {
        $user = User::factory()->create(['peran' => 'user']);

        $this->actingAs($user)
            ->post(route('aset-tik.master.store', 'kategori'), [
                'kode' => 'LTP',
                'nama' => 'Laptop',
                'deskripsi' => 'Perangkat kerja portabel.',
            ])
            ->assertRedirect(route('aset-tik.kategori'));

        $this->actingAs($user)
            ->post(route('aset-tik.master.store', 'lokasi'), [
                'kode' => 'R-101',
                'nama' => 'Ruang 101',
                'gedung' => 'Gedung A',
                'ruangan' => '101',
            ])
            ->assertRedirect(route('aset-tik.lokasi'));

        $this->actingAs($user)
            ->post(route('aset-tik.master.store', 'penanggung-jawab'), [
                'nama' => 'Budi',
                'unit_kerja' => 'TIK',
            ])
            ->assertRedirect(route('aset-tik.penanggung-jawab'));

        $kategori = AsetTikKategori::firstOrFail();
        $lokasi = AsetTikLokasi::firstOrFail();
        $penanggungJawab = AsetTikPenanggungJawab::firstOrFail();

        $this->actingAs($user)
            ->get(route('aset-tik.kategori'))
            ->assertOk()
            ->assertSee('Laptop')
            ->assertSee('LTP');

        $this->actingAs($user)
            ->post(route('aset-tik.data-aset.store'), [
                'kode' => 'AST-001',
                'nama' => 'Laptop Operasional',
                'kategori_id' => $kategori->id,
                'merk' => 'Lenovo',
                'model' => 'ThinkPad',
                'tahun_perolehan' => 2026,
                'nilai' => 15000000,
                'kondisi' => 'Baik',
                'status' => 'Aktif',
                'lokasi_id' => $lokasi->id,
                'penanggung_jawab_id' => $penanggungJawab->id,
            ])
            ->assertRedirect(route('aset-tik.data-aset'));

        $this->assertDatabaseHas('aset_tiks', [
            'kode' => 'AST-001',
            'nama' => 'Laptop Operasional',
            'tracking_code' => 'TIK-AST-001',
        ]);

        $aset = AsetTik::firstOrFail();

        $this->actingAs($user)
            ->get(route('aset-tik.dashboard'))
            ->assertOk()
            ->assertSee('Total Aset')
            ->assertSee('1');

        $this->actingAs($user)
            ->delete(route('aset-tik.data-aset.destroy', $aset))
            ->assertRedirect(route('aset-tik.data-aset'));

        $this->assertDatabaseMissing('aset_tiks', [
            'kode' => 'AST-001',
        ]);
    }

    public function test_user_can_record_asset_transactions_and_history(): void
    {
        $user = User::factory()->create(['peran' => 'user']);
        $kategori = AsetTikKategori::create(['kode' => 'LTP', 'nama' => 'Laptop']);
        $lokasiAsal = AsetTikLokasi::create(['kode' => 'R-101', 'nama' => 'Ruang 101']);
        $lokasiTujuan = AsetTikLokasi::create(['kode' => 'R-202', 'nama' => 'Ruang 202']);
        $penanggungJawab = AsetTikPenanggungJawab::create(['nama' => 'Budi', 'unit_kerja' => 'TIK']);
        $aset = AsetTik::create([
            'kode' => 'AST-001',
            'nama' => 'Laptop Operasional',
            'kategori_id' => $kategori->id,
            'lokasi_id' => $lokasiAsal->id,
            'kondisi' => 'Baik',
            'status' => 'Aktif',
        ]);

        $this->actingAs($user)
            ->post(route('aset-tik.transaksi.store', 'mutasi'), [
                'nomor' => 'MUT-001',
                'tanggal' => '2026-05-29',
                'aset_tik_id' => $aset->id,
                'jenis' => 'Mutasi lokasi',
                'lokasi_asal_id' => $lokasiAsal->id,
                'lokasi_tujuan_id' => $lokasiTujuan->id,
                'penanggung_jawab_baru_id' => $penanggungJawab->id,
                'keterangan' => 'Dipindahkan untuk operasional.',
            ])
            ->assertRedirect(route('aset-tik.mutasi'));

        $aset->refresh();
        $this->assertSame($lokasiTujuan->id, $aset->lokasi_id);
        $this->assertSame($penanggungJawab->id, $aset->penanggung_jawab_id);
        $this->assertDatabaseHas('aset_tik_riwayats', [
            'aset_tik_id' => $aset->id,
            'aktivitas' => 'Mutasi Aset',
        ]);

        $this->actingAs($user)
            ->post(route('aset-tik.transaksi.store', 'maintenance'), [
                'nomor' => 'MNT-001',
                'tanggal' => '2026-05-29',
                'aset_tik_id' => $aset->id,
                'jenis' => 'Corrective',
                'biaya' => 250000,
                'kondisi_akhir' => 'Rusak berat',
            ])
            ->assertRedirect(route('aset-tik.maintenance'));

        $aset->refresh();
        $this->assertSame('Rusak berat', $aset->kondisi);
        $this->assertSame('Rusak', $aset->status);
        $this->assertSame(2, AsetTikRiwayat::where('aset_tik_id', $aset->id)->count());

        $this->actingAs($user)
            ->get(route('aset-tik.riwayat'))
            ->assertOk()
            ->assertSee('Mutasi Aset')
            ->assertSee('Maintenance Aset')
            ->assertSee('MUT-001')
            ->assertSee('MNT-001');
    }

    public function test_asset_reports_show_transaction_and_stock_data(): void
    {
        $user = User::factory()->create(['peran' => 'user']);
        $kategori = AsetTikKategori::create(['kode' => 'SRV', 'nama' => 'Server']);
        $lokasi = AsetTikLokasi::create(['kode' => 'DC', 'nama' => 'Data Center']);
        $aset = AsetTik::create([
            'kode' => 'AST-002',
            'nama' => 'Server Utama',
            'kategori_id' => $kategori->id,
            'lokasi_id' => $lokasi->id,
            'kondisi' => 'Baik',
            'status' => 'Aktif',
        ]);

        $this->actingAs($user)
            ->post(route('aset-tik.transaksi.store', 'aset-masuk'), [
                'nomor' => 'IN-001',
                'tanggal' => '2026-05-29',
                'aset_tik_id' => $aset->id,
                'jenis' => 'Pengadaan',
                'lokasi_tujuan_id' => $lokasi->id,
            ])
            ->assertRedirect(route('aset-tik.aset-masuk'));

        $this->actingAs($user)
            ->get(route('aset-tik.laporan-aset-masuk', ['tahun' => 2026, 'bulan' => 5]))
            ->assertOk()
            ->assertSee('IN-001')
            ->assertSee('Server Utama')
            ->assertSee('Pengadaan');

        $this->actingAs($user)
            ->get(route('aset-tik.laporan-stok'))
            ->assertOk()
            ->assertSee('AST-002')
            ->assertSee('Server Utama')
            ->assertSee('Data Center');
    }

    public function test_user_can_track_asset_by_tracking_code(): void
    {
        $user = User::factory()->create(['peran' => 'user']);
        $kategori = AsetTikKategori::create(['kode' => 'LTP', 'nama' => 'Laptop']);
        $lokasi = AsetTikLokasi::create(['kode' => 'R-101', 'nama' => 'Ruang 101']);
        $aset = AsetTik::create([
            'kode' => 'AST-003',
            'nama' => 'Laptop Tracking',
            'kategori_id' => $kategori->id,
            'lokasi_id' => $lokasi->id,
            'serial_number' => 'SN-TRACK-001',
            'kondisi' => 'Baik',
            'status' => 'Aktif',
        ]);

        $this->actingAs($user)
            ->get(route('aset-tik.qr-tracking', ['q' => $aset->tracking_code]))
            ->assertOk()
            ->assertSee('Laptop Tracking')
            ->assertSee('TIK-AST-003')
            ->assertSee('Ruang 101')
            ->assertSee('Baik');

        $this->actingAs($user)
            ->get(route('aset-tik.qr-tracking', ['q' => 'TIDAK-ADA']))
            ->assertOk()
            ->assertSee('Data aset tidak ditemukan.');
    }

    public function test_user_can_open_asset_tracking_label(): void
    {
        $user = User::factory()->create(['peran' => 'user']);
        $lokasi = AsetTikLokasi::create(['kode' => 'R-101', 'nama' => 'Ruang 101']);
        $aset = AsetTik::create([
            'kode' => 'AST-004',
            'nama' => 'Printer Label',
            'lokasi_id' => $lokasi->id,
            'serial_number' => 'SN-LABEL-001',
            'kondisi' => 'Baik',
            'status' => 'Aktif',
        ]);

        $this->actingAs($user)
            ->get(route('aset-tik.data-aset.label', $aset))
            ->assertOk()
            ->assertSee('Printer Label')
            ->assertSee('TIK-AST-004')
            ->assertSee('SN-LABEL-001')
            ->assertSee('Cetak Label');
    }
}
