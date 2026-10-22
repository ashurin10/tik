<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menus')) {
            $menuIds = DB::table('menus')
                ->where('url', 'database-backup.index')
                ->pluck('id');

            if (Schema::hasTable('menu_access')) {
                DB::table('menu_access')->whereIn('menu_id', $menuIds)->delete();
            }

            DB::table('menus')->whereIn('id', $menuIds)->delete();
        }

        if (Schema::hasTable('services')) {
            DB::table('services')->where('url', 'database-backup.index')->delete();

            DB::table('services')
                ->where('url', 'users.index')
                ->where('description', 'Kelola pengguna, menu aplikasi, dan backup database.')
                ->update([
                    'description' => 'Kelola pengguna dan menu aplikasi.',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // The removed feature is no longer available.
    }
};
