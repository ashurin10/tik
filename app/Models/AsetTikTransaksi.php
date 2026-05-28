<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;

class AsetTikTransaksi extends Model
{
    use HasHashid;

    protected $table = 'aset_tik_transaksis';

    protected $fillable = [
        'tipe',
        'nomor',
        'tanggal',
        'aset_tik_id',
        'jenis',
        'vendor_id',
        'lokasi_asal_id',
        'lokasi_tujuan_id',
        'penanggung_jawab_baru_id',
        'biaya',
        'kondisi_akhir',
        'dokumen',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'biaya' => 'decimal:2',
        ];
    }

    public function aset()
    {
        return $this->belongsTo(AsetTik::class, 'aset_tik_id');
    }

    public function vendor()
    {
        return $this->belongsTo(AsetTikVendor::class, 'vendor_id');
    }

    public function lokasiAsal()
    {
        return $this->belongsTo(AsetTikLokasi::class, 'lokasi_asal_id');
    }

    public function lokasiTujuan()
    {
        return $this->belongsTo(AsetTikLokasi::class, 'lokasi_tujuan_id');
    }

    public function penanggungJawabBaru()
    {
        return $this->belongsTo(AsetTikPenanggungJawab::class, 'penanggung_jawab_baru_id');
    }
}
