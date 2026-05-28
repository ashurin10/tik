<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsetTikRiwayat extends Model
{
    protected $table = 'aset_tik_riwayats';

    protected $fillable = [
        'aset_tik_id',
        'transaksi_id',
        'aktivitas',
        'tanggal',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function aset()
    {
        return $this->belongsTo(AsetTik::class, 'aset_tik_id');
    }

    public function transaksi()
    {
        return $this->belongsTo(AsetTikTransaksi::class, 'transaksi_id');
    }
}
