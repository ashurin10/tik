<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiInventaris extends Model
{
    protected $guarded = [];

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class);
    }
}
