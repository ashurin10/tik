<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menus')) {
            $seededParentIds = DB::table('menus')
                ->whereIn('url', ['#inventory', '#movement', '#maintenance', '#report-audit'])
                ->orWhere(function ($query) {
                    $query->where('url', '#settings')
                        ->where('name', 'Pengaturan');
                })
                ->pluck('id');

            $menuIds = DB::table('menus')
                ->where('url', 'like', 'aset.%')
                ->when($seededParentIds->isNotEmpty(), function ($query) use ($seededParentIds) {
                    $query->orWhereIn('id', $seededParentIds)
                        ->orWhereIn('parent_id', $seededParentIds);
                })
                ->pluck('id');

            if ($menuIds->isNotEmpty()) {
                if (Schema::hasTable('menu_access')) {
                    DB::table('menu_access')->whereIn('menu_id', $menuIds)->delete();
                }

                DB::table('menus')->whereIn('id', $menuIds)->delete();
            }
        }

        Schema::dropIfExists('transaksi_peminjaman');
        Schema::dropIfExists('transaksi_mutasi');
        Schema::dropIfExists('transaksi_masuk');
        Schema::dropIfExists('riwayat_aset');
        Schema::dropIfExists('data_aset');
        Schema::dropIfExists('aset_tik');
        Schema::dropIfExists('asset_categories');
        Schema::dropIfExists('mst_kategori_aset');
        Schema::dropIfExists('mst_kondisi_aset');
        Schema::dropIfExists('mst_klasifikasi_aset');
        Schema::dropIfExists('mst_lokasi');
    }

    public function down(): void
    {
        // Intentionally left blank. The asset module was removed and will be rebuilt from scratch.
    }
};
