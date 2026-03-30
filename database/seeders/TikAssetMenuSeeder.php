<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class TikAssetMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dashboard (Main Hub)
        Menu::firstOrCreate(
            ['url' => 'aset.dashboard'],
            [
                'name' => 'Dashboard',
                'icon' => 'fas fa-chart-pie',
                'order' => 1,
                'parent_id' => null,
                'is_active' => true,
            ]
        );

        // 2. Manajemen Inventaris (Inventory)
        $inventory = Menu::firstOrCreate(
            ['url' => '#inventory'],
            [
                'name' => 'Manajemen Inventaris',
                'icon' => 'fas fa-boxes',
                'order' => 2,
                'parent_id' => null,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.master.data.index'],
            [
                'name' => 'Daftar Aset',
                'icon' => 'fas fa-list',
                'order' => 1,
                'parent_id' => $inventory->id,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.master.data.create'],
            [
                'name' => 'Registrasi Aset Baru',
                'icon' => 'fas fa-plus-circle',
                'order' => 2,
                'parent_id' => $inventory->id,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.cetak-label.index'],
            [
                'name' => 'Cetak Label/Barcode',
                'icon' => 'fas fa-qrcode',
                'order' => 3,
                'parent_id' => $inventory->id,
                'is_active' => true,
            ]
        );

        // 3. Mutasi Aset (Movement)
        $movement = Menu::firstOrCreate(
            ['url' => '#movement'],
            [
                'name' => 'Mutasi Aset',
                'icon' => 'fas fa-exchange-alt',
                'order' => 3,
                'parent_id' => null,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.mutasi.checkout.index'],
            [
                'name' => 'Check-Out (Aset Keluar)',
                'icon' => 'fas fa-sign-out-alt',
                'order' => 1,
                'parent_id' => $movement->id,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.mutasi.checkin.index'],
            [
                'name' => 'Check-In (Aset Masuk)',
                'icon' => 'fas fa-sign-in-alt',
                'order' => 2,
                'parent_id' => $movement->id,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.mutasi.approval.index'],
            [
                'name' => 'Request Approval',
                'icon' => 'fas fa-clipboard-check',
                'order' => 3,
                'parent_id' => $movement->id,
                'is_active' => true,
            ]
        );

        // 4. Pemeliharaan (Maintenance & Lifecycle)
        $maintenance = Menu::firstOrCreate(
            ['url' => '#maintenance'],
            [
                'name' => 'Pemeliharaan',
                'icon' => 'fas fa-tools',
                'order' => 4,
                'parent_id' => null,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.maintenance.jadwal.index'],
            [
                'name' => 'Jadwal Maintenance',
                'icon' => 'fas fa-calendar-check',
                'order' => 1,
                'parent_id' => $maintenance->id,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.maintenance.kondisi.index'],
            [
                'name' => 'Kondisi & Depresiasi',
                'icon' => 'fas fa-heartbeat',
                'order' => 2,
                'parent_id' => $maintenance->id,
                'is_active' => true,
            ]
        );

        // 5. Laporan & Audit (ISO Compliance)
        $report = Menu::firstOrCreate(
            ['url' => '#report-audit'],
            [
                'name' => 'Laporan & Audit',
                'icon' => 'fas fa-file-invoice',
                'order' => 5,
                'parent_id' => null,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.audit.log.index'],
            [
                'name' => 'Log Audit',
                'icon' => 'fas fa-history',
                'order' => 1,
                'parent_id' => $report->id,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.audit.opname.index'],
            [
                'name' => 'Laporan Stok Opname',
                'icon' => 'fas fa-file-excel',
                'order' => 2,
                'parent_id' => $report->id,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.audit.rekap.index'],
            [
                'name' => 'Laporan Keluar-Masuk',
                'icon' => 'fas fa-sync-alt',
                'order' => 3,
                'parent_id' => $report->id,
                'is_active' => true,
            ]
        );

        // 6. Pengaturan (Master Data & User)
        $settings = Menu::firstOrCreate(
            ['url' => '#settings'],
            [
                'name' => 'Pengaturan',
                'icon' => 'fas fa-cogs',
                'order' => 6,
                'parent_id' => null,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'aset.master.kategori.index'],
            [
                'name' => 'Kategori & Lokasi',
                'icon' => 'fas fa-tags',
                'order' => 1,
                'parent_id' => $settings->id,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            ['url' => 'users.index'],
            [
                'name' => 'Manajemen Pengguna',
                'icon' => 'fas fa-users-cog',
                'order' => 2,
                'parent_id' => $settings->id,
                'is_active' => true,
            ]
        );

        // Assign default roles 'admin', 'staff', and 'auditor' to all seeded menus
        // this is important because otherwise AppServiceProvider will hide the menus
        $allMenus = Menu::all();
        foreach ($allMenus as $m) {
            \App\Models\MenuAccess::firstOrCreate(['menu_id' => $m->id, 'role' => 'admin']);
            \App\Models\MenuAccess::firstOrCreate(['menu_id' => $m->id, 'role' => 'staff']);
            \App\Models\MenuAccess::firstOrCreate(['menu_id' => $m->id, 'role' => 'user']);
            \App\Models\MenuAccess::firstOrCreate(['menu_id' => $m->id, 'role' => 'auditor']);
        }
    }
}
