<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasHashid;

class AsetTik extends Model
{
    use HasFactory, SoftDeletes, HasHashid;

    protected $table = 'aset_tik';
    
    protected $appends = ['hashid'];

    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'tahun_pengadaan',
        'kategori',
        'jenis',
        'merk',
        'model_tipe',
        'nomor_seri',
        'spesifikasi',
        'kondisi',
        'status',
        'unit_pengguna',
        'penanggung_jawab',
        'pemilik_aset',
        'lokasi',
        'catatan',
    ];

    protected $casts = [
        'spesifikasi' => 'array', // Jika simpan JSON
    ];

    // Relasi
    public function mutasi()
    {
        return $this->hasMany(TransaksiMutasi::class, 'aset_id');
    }

    public function peminjaman()
    {
        return $this->hasMany(TransaksiPeminjaman::class, 'aset_id');
    }
}
