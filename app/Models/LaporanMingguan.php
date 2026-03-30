<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashid;

class LaporanMingguan extends Model
{
    use HasFactory, HasHashid;
    
    protected $guarded = [];
    protected $appends = ['hashid'];
}
