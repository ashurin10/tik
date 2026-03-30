<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;

$tables = [
    'riwayat_aset',
    'data_aset',
    'mst_klasifikasi_aset',
    'mst_kategori_aset',
    'mst_lokasi'
];

echo "Starting ISO Asset cleanup...\n";

// 1. Drop Tables
foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        Schema::drop($table);
        echo "Dropped table: $table\n";
    }
}

// 2. Remove Migrations
DB::table('migrations')->where('migration', 'like', '%iso_asset_management%')->delete();
echo "Removed migration entries.\n";

// 3. Remove Menu Items
// Assuming 'Manajemen Aset' is the parent
$parentMenu = Menu::where('name', 'Manajemen Aset')->first();
if ($parentMenu) {
    // Delete children first (if cascade doesn't handle it, but usually standard relationships might not be set up for cascade delete on menu self-ref in some setups, safer to do explicit)
    Menu::where('parent_id', $parentMenu->id)->delete();
    $parentMenu->delete();
    echo "Removed 'Manajemen Aset' menu and its submenus.\n";
}

echo "Database cleanup complete.\n";
