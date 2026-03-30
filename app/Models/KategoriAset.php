<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriAset extends Model
{
    use HasFactory;

    protected $table = 'mst_kategori_aset';

    protected $fillable = [
        'nama_kategori',
        'kode_kategori',
        'deskripsi',
    ];

    public function aset()
    {
        return $this->hasMany(Aset::class, 'kategori_id');
    }
}
