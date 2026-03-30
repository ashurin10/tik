<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaporanAktivitasKerja extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    public function laporanMingguan()
    {
        return $this->belongsTo(LaporanMingguan::class, 'laporan_mingguan_id');
    }
}
