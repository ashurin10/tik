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
                ->where('url', 'like', 'inventaris%')
                ->orWhere('name', 'like', '%Inventaris%')
                ->pluck('id');

            if ($menuIds->isNotEmpty()) {
                if (Schema::hasTable('menu_access')) {
                    DB::table('menu_access')->whereIn('menu_id', $menuIds)->delete();
                }

                DB::table('menus')->whereIn('id', $menuIds)->delete();
            }
        }

        Schema::dropIfExists('transaksi_inventaris');
        Schema::dropIfExists('inventaris');
    }

    public function down(): void
    {
        // Intentionally left blank. The inventory module was removed and will be rebuilt from scratch if needed.
    }
};
