<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$m6 = \App\Models\Menu::find(6);
if ($m6) { $m6->name = 'Sirkulasi Aset'; $m6->save(); }

$m7 = \App\Models\Menu::find(7);
if ($m7) { $m7->name = 'Aset Keluar (Peminjaman)'; $m7->save(); }

$m8 = \App\Models\Menu::find(8); 
if ($m8) { $m8->name = 'Aset Masuk (Pengembalian)'; $m8->save(); }

$m_new = \App\Models\Menu::create(['name' => 'Aset Masuk (Pengadaan)', 'url' => 'aset.mutasi.penerimaan.index', 'icon' => 'fas fa-box-open', 'parent_id' => 6, 'order' => 0, 'is_active' => 1]);
\App\Models\MenuAccess::create(['menu_id' => $m_new->id, 'role' => 'admin']);
\App\Models\MenuAccess::create(['menu_id' => $m_new->id, 'role' => 'user']);
echo "Menus updated.\n";
