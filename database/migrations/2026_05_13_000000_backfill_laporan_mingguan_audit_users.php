<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('laporan_mingguans') || !Schema::hasTable('users')) {
            return;
        }

        $fallbackUserId = DB::table('users')->orderBy('id')->value('id');
        if (!$fallbackUserId) {
            return;
        }

        if (Schema::hasColumn('laporan_mingguans', 'created_by')) {
            DB::table('laporan_mingguans')
                ->whereNull('created_by')
                ->update(['created_by' => $fallbackUserId]);
        }

        if (Schema::hasColumn('laporan_mingguans', 'updated_by')) {
            DB::table('laporan_mingguans')
                ->whereNull('updated_by')
                ->update(['updated_by' => $fallbackUserId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backfill tidak dibalik agar riwayat audit yang sudah terisi tetap aman.
    }
};
