<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    protected $guarded = [];

    public function getRouteKeyName()
    {
        return 'short_code';
    }
}
