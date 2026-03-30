<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiMutasi extends Model
{
    use HasFactory;

    protected $table = 'transaksi_mutasi';

    protected $fillable = [
        'no_mutasi',
        'aset_id',
        'tanggal_mutasi',
        'unit_asal',
        'unit_tujuan',
        'penanggung_jawab_baru',
        'jenis_mutasi',
        'alasan',
    ];

    protected $casts = [
        'tanggal_mutasi' => 'date',
    ];

    public function aset()
    {
        return $this->belongsTo(AsetTik::class, 'aset_id');
    }
}
