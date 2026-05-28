<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AsetTik extends Model
{
    use HasHashid;

    protected $table = 'aset_tiks';

    protected $fillable = [
        'kode',
        'tracking_code',
        'nama',
        'kategori_id',
        'merk',
        'model',
        'serial_number',
        'spesifikasi',
        'tahun_perolehan',
        'nilai',
        'kondisi',
        'status',
        'lokasi_id',
        'penanggung_jawab_id',
        'keterangan',
    ];

    protected static function booted(): void
    {
        static::creating(function (AsetTik $aset) {
            if (!$aset->tracking_code) {
                $aset->tracking_code = static::makeTrackingCode($aset->kode);
            }
        });
    }

    public static function makeTrackingCode(string $kode): string
    {
        return 'TIK-' . Str::of($kode)->upper()->replaceMatches('/[^A-Z0-9]+/', '-')->trim('-');
    }

    public function kategori()
    {
        return $this->belongsTo(AsetTikKategori::class, 'kategori_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(AsetTikLokasi::class, 'lokasi_id');
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(AsetTikPenanggungJawab::class, 'penanggung_jawab_id');
    }

    public function transaksi()
    {
        return $this->hasMany(AsetTikTransaksi::class, 'aset_tik_id');
    }

    public function riwayat()
    {
        return $this->hasMany(AsetTikRiwayat::class, 'aset_tik_id');
    }
}
