<?php

namespace App\Services\Asset;

use App\Models\AsetTik;
use App\Services\UniqueCodeService;
use Illuminate\Support\Facades\DB;
use Exception;

class MasterAsetService
{
    public function getFilteredAssets(string $search = null, string $kategori = null, string $status = null)
    {
        $query = AsetTik::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_aset', 'like', "%{$search}%")
                    ->orWhere('kode_aset', 'like', "%{$search}%")
                    ->orWhere('merk', 'like', "%{$search}%")
                    ->orWhere('nomor_seri', 'like', "%{$search}%");
            });
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate(10);
    }

    public function generateTemplateCallback()
    {
        $headers = [
            'nama_aset', 'tahun_pengadaan(YYYY)', 'kategori(Hardware/Software/Jaringan)', 'jenis', 'merk',
            'model_tipe', 'nomor_seri', 'kondisi(Baik/Cukup/Rusak)',
            'status(Aktif/Terpakai/Maintenance/Pensiun)', 'unit_pengguna',
            'penanggung_jawab', 'pemilik_aset', 'lokasi', 'catatan'
        ];

        return function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, [
                'Laptop Dell Latitude 5520', date('Y'), 'Hardware', 'Laptop', 'Dell',
                'Latitude 5520', 'SN-123456', 'Baik', 'Aktif',
                'Divisi IT', 'Bapak Budi', 'PT. ABC', 'Ruang Server TIK',
                'Aset pengadaan baru Q1'
            ]);
            fclose($file);
        };
    }

    public function importCsv($file)
    {
        DB::beginTransaction();
        try {
            $path = $file->getRealPath();
            $handle = fopen($path, 'r');

            if (!$handle) {
                throw new Exception('Tidak dapat membaca file CSV.');
            }

            $header = fgetcsv($handle);
            $expectedHeader = [
                'nama_aset', 'tahun_pengadaan(YYYY)', 'kategori(Hardware/Software/Jaringan)', 'jenis', 'merk',
                'model_tipe', 'nomor_seri', 'kondisi(Baik/Cukup/Rusak)',
                'status(Aktif/Terpakai/Maintenance/Pensiun)', 'unit_pengguna',
                'penanggung_jawab', 'pemilik_aset', 'lokasi', 'catatan'
            ];

            if ($header !== $expectedHeader) {
                fclose($handle);
                throw new Exception('Format header CSV tidak sesuai template. Silakan unduh template terbaru.');
            }

            $importedCount = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 14 || empty(trim($row[0]))) {
                    continue;
                }

                $kodeAset = UniqueCodeService::generateAssetCode();

                AsetTik::create([
                    'kode_aset' => $kodeAset,
                    'nama_aset' => trim($row[0]),
                    'tahun_pengadaan' => trim($row[1]) ? intval(trim($row[1])) : null,
                    'kategori' => trim($row[2]) ?: 'Hardware',
                    'jenis' => trim($row[3]) ?: '-',
                    'merk' => trim($row[4]) ?: null,
                    'model_tipe' => trim($row[5]) ?: null,
                    'nomor_seri' => trim($row[6]) ?: null,
                    'kondisi' => trim($row[7]) ?: 'Baik',
                    'status' => trim($row[8]) ?: 'Aktif',
                    'unit_pengguna' => trim($row[9]) ?: '-',
                    'penanggung_jawab' => trim($row[10]) ?: '-',
                    'pemilik_aset' => trim($row[11] ?? '') ?: null,
                    'lokasi' => trim($row[12] ?? '') ?: null,
                    'catatan' => trim($row[13] ?? '') ?: null,
                ]);
                $importedCount++;
            }

            fclose($handle);
            DB::commit();

            return $importedCount;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createAsset(array $data)
    {
        DB::beginTransaction();
        try {
            if (empty($data['kode_aset'])) {
                $data['kode_aset'] = UniqueCodeService::generateAssetCode();
            }

            if (isset($data['spesifikasi']) && is_array($data['spesifikasi'])) {
                $specs = array_filter($data['spesifikasi'], fn($value) => !is_null($value) && $value !== '');
                $data['spesifikasi'] = !empty($specs) ? $specs : null;
            }

            $asset = AsetTik::create($data);
            DB::commit();

            return $asset;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateAsset(AsetTik $aset, array $data)
    {
        if (isset($data['spesifikasi']) && is_array($data['spesifikasi'])) {
            $specs = array_filter($data['spesifikasi'], fn($value) => !is_null($value) && $value !== '');
            $data['spesifikasi'] = !empty($specs) ? $specs : null;
        }

        $aset->update($data);
        return $aset;
    }
}
