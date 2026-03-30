<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiMasuk extends Model
{
    use HasFactory;

    protected $table = 'transaksi_masuk';

    protected $fillable = [
        'no_transaksi',
        'tanggal_masuk',
        'sumber_aset',
        'no_dokumen_spk',
        'diterima_oleh',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
    ];
}
