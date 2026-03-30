<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    protected $guarded = [];

    public function transaksi()
    {
        return $this->hasMany(TransaksiInventaris::class);
    }

    public function getRouteKeyName()
    {
        return 'kode_barang';
    }
}
