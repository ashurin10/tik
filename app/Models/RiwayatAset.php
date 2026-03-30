<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatAset extends Model
{
    use HasFactory;

    protected $table = 'riwayat_aset';

    public $timestamps = false; // Custom timestamp col

    protected $fillable = [
        'aset_id',
        'user_id',
        'jenis_perubahan',
        'deskripsi_perubahan',
        'tanggal_perubahan',
    ];

    protected $casts = [
        'tanggal_perubahan' => 'datetime',
    ];

    public function aset()
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
