<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;

class AsetTikLokasi extends Model
{
    use HasHashid;

    protected $table = 'aset_tik_lokasis';

    protected $fillable = ['kode', 'nama', 'gedung', 'ruangan', 'keterangan'];
}
