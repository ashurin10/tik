<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('services')) {
            return;
        }

        DB::table('services')
            ->where('url', 'like', 'aset.%')
            ->orWhere('url', 'like', 'inventaris%')
            ->delete();

        $now = now();
        $services = [
            [
                'title' => 'Laporan Mingguan',
                'category' => 'Pelaporan',
                'description' => 'Kelola, impor, dan cetak laporan kegiatan mingguan.',
                'icon_class' => 'fas fa-calendar-check',
                'url' => 'laporan-mingguan.index',
                'order' => 10,
            ],
            [
                'title' => 'URL Shortener',
                'category' => 'Utilitas',
                'description' => 'Buat dan kelola tautan pendek untuk kebutuhan internal.',
                'icon_class' => 'fas fa-link',
                'url' => 'urls.index',
                'order' => 20,
            ],
            [
                'title' => 'Manajemen Pengguna',
                'category' => 'Administrasi',
                'description' => 'Atur akun, role, status aktif, dan pembukaan kunci pengguna.',
                'icon_class' => 'fas fa-users-cog',
                'url' => 'users.index',
                'order' => 30,
            ],
            [
                'title' => 'Manajemen Menu',
                'category' => 'Administrasi',
                'description' => 'Atur menu sidebar untuk sistem yang sudah masuk aplikasi.',
                'icon_class' => 'fas fa-bars',
                'url' => 'menus.index',
                'order' => 40,
            ],
            [
                'title' => 'Backup Database',
                'category' => 'Administrasi',
                'description' => 'Unduh backup database SQLite saat diperlukan.',
                'icon_class' => 'fas fa-database',
                'url' => 'database-backup.index',
                'order' => 50,
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['url' => $service['url']],
                $service + ['updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('services')) {
            return;
        }

        DB::table('services')
            ->whereIn('url', [
                'laporan-mingguan.index',
                'urls.index',
                'users.index',
                'menus.index',
                'database-backup.index',
            ])
            ->delete();
    }
};
