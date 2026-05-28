<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;

class AsetTikKategori extends Model
{
    use HasHashid;

    protected $table = 'aset_tik_kategoris';

    protected $fillable = ['kode', 'nama', 'deskripsi'];
}
