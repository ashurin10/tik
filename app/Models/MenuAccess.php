<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuAccess extends Model
{
    use HasFactory;

    protected $table = 'menu_access';

    protected $fillable = [
        'menu_id',
        'role',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
