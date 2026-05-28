<?php

use App\Models\Menu;
use App\Models\MenuAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menus') || !Schema::hasTable('menu_access')) {
            return;
        }

        $menus = [
            ['name' => 'URL Shortener', 'url' => 'urls.index', 'icon' => 'fas fa-link', 'order' => 20, 'roles' => ['admin', 'user']],
            ['name' => 'Manajemen Pengguna', 'url' => 'users.index', 'icon' => 'fas fa-users-cog', 'order' => 30, 'roles' => ['admin']],
            ['name' => 'Manajemen Menu', 'url' => 'menus.index', 'icon' => 'fas fa-bars', 'order' => 40, 'roles' => ['admin']],
        ];

        foreach ($menus as $item) {
            $menu = Menu::updateOrCreate(
                ['url' => $item['url']],
                [
                    'name' => $item['name'],
                    'icon' => $item['icon'],
                    'order' => $item['order'],
                    'parent_id' => null,
                    'is_active' => true,
                ]
            );

            foreach ($item['roles'] as $role) {
                MenuAccess::firstOrCreate([
                    'menu_id' => $menu->id,
                    'role' => $role,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }

        Menu::whereIn('url', ['urls.index', 'users.index', 'menus.index'])->delete();
    }
};
