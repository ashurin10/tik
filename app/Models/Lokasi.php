<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'mst_lokasi';

    protected $fillable = [
        'nama_lokasi',
        'alamat',
    ];

    public function aset()
    {
        return $this->hasMany(Aset::class, 'lokasi_id');
    }
}
