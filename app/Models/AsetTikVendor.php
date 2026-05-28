<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;

class AsetTikVendor extends Model
{
    use HasHashid;

    protected $table = 'aset_tik_vendors';

    protected $fillable = ['nama', 'alamat', 'kontak', 'pic'];
}
