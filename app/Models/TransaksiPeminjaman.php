<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiPeminjaman extends Model
{
    use HasFactory;

    protected $table = 'transaksi_peminjaman';

    protected $fillable = [
        'no_peminjaman',
        'aset_id',
        'nama_peminjam',
        'tanggal_pinjam',
        'rencana_kembali',
        'tanggal_kembali',
        'kondisi_saat_kembali',
        'status',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'rencana_kembali' => 'date',
        'tanggal_kembali' => 'date',
    ];

    public function aset()
    {
        return $this->belongsTo(AsetTik::class, 'aset_id');
    }
}
