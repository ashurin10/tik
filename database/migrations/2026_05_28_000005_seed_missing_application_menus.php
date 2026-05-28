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

        $laporan = Menu::updateOrCreate(
            ['url' => null, 'name' => 'Laporan Mingguan'],
            [
                'icon' => 'fas fa-calendar-check',
                'order' => 10,
                'parent_id' => null,
                'is_active' => true,
            ]
        );

        $items = [
            ['name' => 'Data Laporan', 'url' => 'laporan-mingguan.index', 'icon' => 'fas fa-list', 'order' => 10, 'parent_id' => $laporan->id, 'roles' => ['admin', 'user']],
            ['name' => 'Dashboard Laporan', 'url' => 'laporan-mingguan.dashboard', 'icon' => 'fas fa-chart-pie', 'order' => 20, 'parent_id' => $laporan->id, 'roles' => ['admin', 'user']],
            ['name' => 'URL Shortener', 'url' => 'urls.index', 'icon' => 'fas fa-link', 'order' => 20, 'parent_id' => null, 'roles' => ['admin', 'user']],
            ['name' => 'Manajemen Pengguna', 'url' => 'users.index', 'icon' => 'fas fa-users-cog', 'order' => 30, 'parent_id' => null, 'roles' => ['admin']],
            ['name' => 'Manajemen Menu', 'url' => 'menus.index', 'icon' => 'fas fa-bars', 'order' => 40, 'parent_id' => null, 'roles' => ['admin']],
            ['name' => 'Backup Database', 'url' => 'database-backup.index', 'icon' => 'fas fa-database', 'order' => 50, 'parent_id' => null, 'roles' => ['admin']],
        ];

        foreach ($items as $item) {
            $menu = Menu::updateOrCreate(
                ['url' => $item['url']],
                [
                    'name' => $item['name'],
                    'icon' => $item['icon'],
                    'order' => $item['order'],
                    'parent_id' => $item['parent_id'],
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

        foreach (['admin', 'user'] as $role) {
            MenuAccess::firstOrCreate([
                'menu_id' => $laporan->id,
                'role' => $role,
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
