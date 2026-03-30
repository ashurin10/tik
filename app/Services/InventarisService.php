<?php

namespace App\Services;

use App\Models\Inventaris;
use App\Models\TransaksiInventaris;
use Illuminate\Support\Facades\DB;
use Exception;

class InventarisService
{
    public function storeTransaksi(Inventaris $inventaris, array $data)
    {
        return DB::transaction(function () use ($inventaris, $data) {
            $jumlah = (int) $data['jumlah'];
            $stokAwal = $inventaris->stok;

            if ($data['jenis_transaksi'] === 'masuk') {
                $inventaris->stok += $jumlah;
            } else {
                if ($inventaris->stok < $jumlah) {
                    throw new Exception("Stok tidak mencukupi. Stok saat ini: {$stokAwal}");
                }
                $inventaris->stok -= $jumlah;
            }

            $inventaris->save();

            return TransaksiInventaris::create([
                'inventaris_id' => $inventaris->id,
                'jenis_transaksi' => $data['jenis_transaksi'],
                'jumlah' => $jumlah,
                'sisa_stok' => $inventaris->stok,
                'tanggal' => $data['tanggal'],
                'pic' => $data['pic'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);
        });
    }
}
