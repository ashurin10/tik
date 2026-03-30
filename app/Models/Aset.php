<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'data_aset';

    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kategori_id',
        'lokasi_id',
        'nomor_seri',
        'model',
        'merk',
        'spesifikasi_teknis', // Cast as array
        'tanggal_perolehan',
        'harga_perolehan',
        'habis_masa_garansi',
        'pemilik_id',
        'pengguna_id',
        'divisi',
        'klasifikasi_id',
        'nilai_kerahasiaan',
        'nilai_integritas',
        'nilai_ketersediaan',
        'ancaman',
        'kontrol_eksisting',
        'tingkat_risiko',
        'status',
        'created_by',
    ];

    protected $casts = [
        'spesifikasi_teknis' => 'array',
        'tanggal_perolehan' => 'date',
        'habis_masa_garansi' => 'date',
    ];

    // Relationships
    public function kategori()
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function klasifikasi()
    {
        return $this->belongsTo(KlasifikasiAset::class, 'klasifikasi_id');
    }

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
