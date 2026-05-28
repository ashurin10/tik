<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;

class AsetTikPenanggungJawab extends Model
{
    use HasHashid;

    protected $table = 'aset_tik_penanggung_jawabs';

    protected $fillable = ['nama', 'unit_kerja'];
}
