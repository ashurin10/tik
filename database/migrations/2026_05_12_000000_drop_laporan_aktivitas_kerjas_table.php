<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('menus')) {
            Menu::query()
                ->where('url', 'like', 'laporan-aktivitas-kerja%')
                ->orWhere('name', 'like', '%Aktivitas Kerja%')
                ->delete();
        }

        Schema::dropIfExists('laporan_aktivitas_kerjas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Fitur Laporan Aktivitas Kerja sudah dihapus dari aplikasi.
        // Migration ini tidak membuat ulang tabel lama saat rollback.
    }
};
