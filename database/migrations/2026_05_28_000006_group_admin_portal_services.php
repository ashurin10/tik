<?php

use App\Models\Menu;
use App\Models\MenuAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('services')) {
            DB::table('services')
                ->whereIn('url', ['users.index', 'menus.index', 'database-backup.index'])
                ->delete();

            DB::table('services')->updateOrInsert(
                ['url' => 'users.index'],
                [
                    'title' => 'Panel Admin',
                    'category' => 'Administrasi',
                    'description' => 'Kelola pengguna, menu aplikasi, dan backup database.',
                    'icon_class' => 'fas fa-user-shield',
                    'order' => 30,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        if (!Schema::hasTable('menus') || !Schema::hasTable('menu_access')) {
            return;
        }

        Menu::whereIn('url', ['users.index', 'menus.index', 'database-backup.index'])
            ->get()
            ->each(function (Menu $menu) {
                foreach (['admin', 'superadmin'] as $role) {
                    MenuAccess::firstOrCreate([
                        'menu_id' => $menu->id,
                        'role' => $role,
                    ]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('services')) {
            return;
        }

        DB::table('services')->where('url', 'users.index')->delete();
    }
};
