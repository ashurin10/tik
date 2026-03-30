<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashid;

class Menu extends Model
{
    use HasFactory, HasHashid;

    protected $fillable = [
        'name',
        'url',
        'icon',
        'parent_id',
        'order',
        'is_active',
    ];

    protected $appends = ['hashid'];

    /**
     * Get parent menu.
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Get child menus (submenus).
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get roles that have access to this menu.
     */
    public function roles()
    {
        // Since we used a custom table 'menu_access' and 'role' is a string column,
        // we can't use standard belongsToMany with a Role model if Role model doesn't exist.
        // But we can model the access simply.
        return $this->hasMany(MenuAccess::class);
    }
}
