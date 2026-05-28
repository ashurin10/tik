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
            DB::table('services')->updateOrInsert(
                ['url' => 'aset-tik.dashboard'],
                [
                    'title' => 'Manajemen Aset TIK',
                    'category' => 'Aset',
                    'description' => 'Kelola aset, mutasi, maintenance, tracking, dan laporan aset TIK.',
                    'icon_class' => 'fas fa-laptop-code',
                    'order' => 25,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        if (!Schema::hasTable('menus') || !Schema::hasTable('menu_access')) {
            return;
        }

        $roles = ['admin', 'superadmin', 'user'];

        $parents = [
            [
                'name' => 'Dashboard Aset',
                'url' => 'aset-tik.dashboard',
                'icon' => 'fas fa-chart-line',
                'order' => 10,
                'children' => [],
            ],
            [
                'name' => 'Master Data',
                'url' => null,
                'icon' => 'fas fa-database',
                'order' => 20,
                'children' => [
                    ['name' => 'Kategori Aset', 'url' => 'aset-tik.kategori', 'icon' => 'fas fa-tags', 'order' => 10],
                    ['name' => 'Data Aset TIK', 'url' => 'aset-tik.data-aset', 'icon' => 'fas fa-laptop', 'order' => 20],
                    ['name' => 'Lokasi Aset', 'url' => 'aset-tik.lokasi', 'icon' => 'fas fa-map-marker-alt', 'order' => 30],
                    ['name' => 'Penanggung Jawab', 'url' => 'aset-tik.penanggung-jawab', 'icon' => 'fas fa-user-tie', 'order' => 40],
                    ['name' => 'Vendor/Supplier', 'url' => 'aset-tik.vendor', 'icon' => 'fas fa-building', 'order' => 50],
                ],
            ],
            [
                'name' => 'Transaksi Aset',
                'url' => null,
                'icon' => 'fas fa-random',
                'order' => 30,
                'children' => [
                    ['name' => 'Aset Masuk', 'url' => 'aset-tik.aset-masuk', 'icon' => 'fas fa-sign-in-alt', 'order' => 10],
                    ['name' => 'Aset Keluar', 'url' => 'aset-tik.aset-keluar', 'icon' => 'fas fa-sign-out-alt', 'order' => 20],
                    ['name' => 'Mutasi Aset', 'url' => 'aset-tik.mutasi', 'icon' => 'fas fa-exchange-alt', 'order' => 30],
                    ['name' => 'Maintenance Aset', 'url' => 'aset-tik.maintenance', 'icon' => 'fas fa-tools', 'order' => 40],
                    ['name' => 'Penghapusan Aset', 'url' => 'aset-tik.penghapusan', 'icon' => 'fas fa-trash-alt', 'order' => 50],
                ],
            ],
            [
                'name' => 'Monitoring',
                'url' => null,
                'icon' => 'fas fa-search-location',
                'order' => 40,
                'children' => [
                    ['name' => 'Riwayat Aset', 'url' => 'aset-tik.riwayat', 'icon' => 'fas fa-history', 'order' => 10],
                    ['name' => 'QR/Barcode Tracking', 'url' => 'aset-tik.qr-tracking', 'icon' => 'fas fa-qrcode', 'order' => 20],
                ],
            ],
            [
                'name' => 'Laporan Aset',
                'url' => null,
                'icon' => 'fas fa-file-alt',
                'order' => 50,
                'children' => [
                    ['name' => 'Laporan Aset Masuk', 'url' => 'aset-tik.laporan-aset-masuk', 'icon' => 'fas fa-file-import', 'order' => 10],
                    ['name' => 'Laporan Aset Keluar', 'url' => 'aset-tik.laporan-aset-keluar', 'icon' => 'fas fa-file-export', 'order' => 20],
                    ['name' => 'Laporan Sisa/Stok', 'url' => 'aset-tik.laporan-stok', 'icon' => 'fas fa-boxes', 'order' => 30],
                    ['name' => 'Laporan Aset Terpakai', 'url' => 'aset-tik.laporan-terpakai', 'icon' => 'fas fa-clipboard-list', 'order' => 40],
                ],
            ],
        ];

        foreach ($parents as $parentData) {
            $parent = Menu::updateOrCreate(
                ['name' => $parentData['name'], 'url' => $parentData['url']],
                [
                    'icon' => $parentData['icon'],
                    'parent_id' => null,
                    'order' => $parentData['order'],
                    'is_active' => true,
                ]
            );

            $this->syncRoles($parent, $roles);

            foreach ($parentData['children'] as $childData) {
                $child = Menu::updateOrCreate(
                    ['name' => $childData['name'], 'parent_id' => $parent->id],
                    [
                        'url' => $childData['url'],
                        'icon' => $childData['icon'],
                        'order' => $childData['order'],
                        'is_active' => true,
                    ]
                );

                $this->syncRoles($child, $roles);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('services')) {
            DB::table('services')->where('url', 'aset-tik.dashboard')->delete();
        }

        if (!Schema::hasTable('menus')) {
            return;
        }

        Menu::where('url', 'aset-tik.dashboard')
            ->orWhere('url', 'like', 'aset-tik.%')
            ->orWhereIn('name', ['Master Data', 'Transaksi Aset', 'Monitoring', 'Laporan Aset'])
            ->delete();
    }

    private function syncRoles(Menu $menu, array $roles): void
    {
        foreach ($roles as $role) {
            MenuAccess::firstOrCreate([
                'menu_id' => $menu->id,
                'role' => $role,
            ]);
        }
    }
};
