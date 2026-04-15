<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Aset
        Schema::create('aset_tik', function (Blueprint $table) {
            $table->id();

            // Identifikasi
            $table->string('kode_aset')->unique(); // INV-2025-001
            $table->string('nama_aset');
            $table->string('kategori'); // Hardware, Software, dll
            $table->string('jenis');    // Laptop, Server, dll

            // Detail Fisik
            $table->string('merk')->nullable();
            $table->string('model_tipe')->nullable();
            $table->string('nomor_seri')->nullable();
            $table->text('spesifikasi')->nullable(); // JSON or Text

            // Pengadaan & Kondisi
            $table->year('tahun_pengadaan')->nullable();
            $table->date('garansi_sampai')->nullable();
            $table->enum('kondisi', ['Baik', 'Cukup', 'Rusak'])->default('Baik');
            $table->enum('status', ['Aktif', 'Terpakai', 'Maintenance', 'Pensiun', 'Dihapus'])->default('Aktif');

            // Kepemilikan & Lokasi
            $table->string('unit_pengguna')->nullable(); // Nama Unit/Divisi
            $table->string('penanggung_jawab')->nullable(); // Nama PIC
            $table->string('lokasi')->nullable(); // Ruangan/Gedung

            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Transaksi Aset Masuk
        Schema::create('transaksi_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique(); // TM-2025-001
            $table->date('tanggal_masuk');
            $table->string('sumber_aset'); // Pengadaan, Hibah, dll
            $table->string('no_dokumen_spk')->nullable();
            $table->string('diterima_oleh')->nullable();
            $table->text('keterangan')->nullable();

            // Relasi opsional jika tracking batch, tapi di sini aset dibuat satu per satu atau batch?
            // Untuk simplifikasi sesuai plan, kita catat header transaksi saja dulu, 
            // detail aset akan punya relasi ke sini jika perlu (tapi di plan awal field aset tidak ada transaction_id, kita skip dulu relasi direct).

            $table->timestamps();
        });

        // 3. Mutasi & Penghapusan
        Schema::create('transaksi_mutasi', function (Blueprint $table) {
            $table->id();
            $table->string('no_mutasi')->unique(); // MUT-2025-001

            $table->foreignId('aset_id')->constrained('aset_tik');

            $table->date('tanggal_mutasi');
            $table->string('unit_asal')->nullable();
            $table->string('unit_tujuan')->nullable();
            $table->string('penanggung_jawab_baru')->nullable();

            $table->enum('jenis_mutasi', ['Antar Unit', 'Penghapusan'])->default('Antar Unit');
            $table->text('alasan')->nullable();

            $table->timestamps();
        });

        // 4. Peminjaman
        Schema::create('transaksi_peminjaman', function (Blueprint $table) {
            $table->id();
            $table->string('no_peminjaman')->unique(); // PIN-2025-001

            $table->foreignId('aset_id')->constrained('aset_tik');

            $table->string('nama_peminjam');
            $table->date('tanggal_pinjam');
            $table->date('rencana_kembali');

            // Pengembalian
            $table->date('tanggal_kembali')->nullable();
            $table->string('kondisi_saat_kembali')->nullable();

            $table->enum('status', ['Dipinjam', 'Dikembalikan'])->default('Dipinjam');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_peminjaman');
        Schema::dropIfExists('transaksi_mutasi');
        Schema::dropIfExists('transaksi_masuk');
        Schema::dropIfExists('aset_tik');
    }
};
