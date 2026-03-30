<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuAccess;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dashboard
        $dashboard = Menu::create([
            'name' => 'Dashboard',
            'url' => 'dashboard',
            'icon' => 'fas fa-home',
            'order' => 1,
        ]);
        MenuAccess::create(['menu_id' => $dashboard->id, 'role' => 'admin']);
        MenuAccess::create(['menu_id' => $dashboard->id, 'role' => 'user']);

        // 2. User Management
        $users = Menu::create([
            'name' => 'Users',
            'url' => 'users.index',
            'icon' => 'fas fa-user-friends',
            'order' => 2,
        ]);
        MenuAccess::create(['menu_id' => $users->id, 'role' => 'admin']);

        // 3. Menu Management (So we can manage this!)
        $menus = Menu::create([
            'name' => 'Menu Management',
            'url' => 'menus.index',
            'icon' => 'fas fa-bars',
            'order' => 3,
        ]);
        MenuAccess::create(['menu_id' => $menus->id, 'role' => 'admin']);

        // 4. Tables (Example)
        $tables = Menu::create([
            'name' => 'Tables',
            'url' => '#',
            'icon' => 'fas fa-table',
            'order' => 4,
        ]);
        MenuAccess::create(['menu_id' => $tables->id, 'role' => 'admin']);
        MenuAccess::create(['menu_id' => $tables->id, 'role' => 'user']);
    }
}
