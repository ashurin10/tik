<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IsoMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Kategori (Categories)
        $categories = [
            ['kode' => 'HW01', 'nama' => 'Hardware - Laptop & PC', 'deskripsi' => 'Perangkat Komputer'],
            ['kode' => 'HW02', 'nama' => 'Hardware - Server', 'deskripsi' => 'Perangkat Data Center'],
            ['kode' => 'HW03', 'nama' => 'Hardware - Network', 'deskripsi' => 'Router, Switch, Firewall'],
            ['kode' => 'SW01', 'nama' => 'Software - OS', 'deskripsi' => 'Sistem Operasi'],
            ['kode' => 'SW02', 'nama' => 'Software - App', 'deskripsi' => 'Aplikasi Bisnis'],
            ['kode' => 'LIC01', 'nama' => 'License', 'deskripsi' => 'Lisensi Software'],
            ['kode' => 'DOC01', 'nama' => 'Document', 'deskripsi' => 'Dokumen Penting ISO'],
        ];

        foreach ($categories as $cat) {
            DB::table('mst_kategori_aset')->updateOrInsert(
                ['kode' => $cat['kode']],
                ['nama' => $cat['nama'], 'deskripsi' => $cat['deskripsi']]
            );
        }

        // 2. Kondisi (Conditions)
        $conditions = [
            ['kondisi' => 'Baik', 'deskripsi' => 'Berfungsi normal tanpa cacat'],
            ['kondisi' => 'Rusak Ringan', 'deskripsi' => 'Berfungsi sebagian, perlu perbaikan minor'],
            ['kondisi' => 'Rusak Berat', 'deskripsi' => 'Tidak berfungsi, perlu perbaikan major atau penggantian'],
            ['kondisi' => 'Hilang', 'deskripsi' => 'Aset tidak ditemukan saat audit'],
        ];

        foreach ($conditions as $cond) {
            DB::table('mst_kondisi_aset')->updateOrInsert(
                ['kondisi' => $cond['kondisi']],
                ['deskripsi' => $cond['deskripsi']]
            );
        }

        // 3. Klasifikasi (Classifications) - ISO 27001
        $classifications = [
            ['nama' => 'Public', 'level_risiko' => 1, 'deskripsi' => 'Informasi yang boleh diketahui publik'],
            ['nama' => 'Internal', 'level_risiko' => 2, 'deskripsi' => 'Informasi internal perusahaan, tidak boleh keluar'],
            ['nama' => 'Confidential', 'level_risiko' => 3, 'deskripsi' => 'Sangat rahasia, akses terbatas'],
            ['nama' => 'Critical', 'level_risiko' => 4, 'deskripsi' => 'Kritis bagi kelangsungan bisnis'],
        ];

        foreach ($classifications as $cls) {
            DB::table('mst_klasifikasi_aset')->updateOrInsert(
                ['nama' => $cls['nama']],
                ['level_risiko' => $cls['level_risiko'], 'deskripsi' => $cls['deskripsi']]
            );
        }

        // 4. Lokasi (Locations) - Sample
        $locations = [
            ['nama_lokasi' => 'Server Room Utama', 'gedung' => 'Gedung A', 'lantai' => 'LT 1', 'ruangan' => 'R. Server 101'],
            ['nama_lokasi' => 'Ruang Staff IT', 'gedung' => 'Gedung A', 'lantai' => 'LT 2', 'ruangan' => 'R. 205'],
            ['nama_lokasi' => 'Gudang Aset', 'gedung' => 'Gedung B', 'lantai' => 'LT Basement', 'ruangan' => 'B. 05'],
        ];

        foreach ($locations as $loc) {
            DB::table('mst_lokasi')->updateOrInsert(
                ['nama_lokasi' => $loc['nama_lokasi']],
                ['gedung' => $loc['gedung'], 'lantai' => $loc['lantai'], 'ruangan' => $loc['ruangan']]
            );
        }
    }
}
