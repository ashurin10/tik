<?php

use App\Models\Menu;
use App\Models\MenuAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('menus') || !Schema::hasTable('menu_access')) {
            return;
        }

        $menu = Menu::firstOrCreate(
            ['url' => 'database-backup.index'],
            [
                'name' => 'Backup Database',
                'icon' => 'fas fa-database',
                'order' => (Menu::max('order') ?? 0) + 1,
                'is_active' => true,
            ]
        );

        MenuAccess::firstOrCreate([
            'menu_id' => $menu->id,
            'role' => 'admin',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }

        Menu::where('url', 'database-backup.index')->delete();
    }
};
