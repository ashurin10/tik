<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KlasifikasiAset extends Model
{
    use HasFactory;

    protected $table = 'mst_klasifikasi_aset';

    protected $fillable = [
        'nama_klasifikasi',
        'level_risiko',
        'deskripsi',
    ];

    public function aset()
    {
        return $this->hasMany(Aset::class, 'klasifikasi_id');
    }
}
