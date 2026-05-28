<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aset_tik_transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('tipe');
            $table->string('nomor')->unique();
            $table->date('tanggal');
            $table->foreignId('aset_tik_id')->constrained('aset_tiks')->cascadeOnDelete();
            $table->string('jenis')->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained('aset_tik_vendors')->nullOnDelete();
            $table->foreignId('lokasi_asal_id')->nullable()->constrained('aset_tik_lokasis')->nullOnDelete();
            $table->foreignId('lokasi_tujuan_id')->nullable()->constrained('aset_tik_lokasis')->nullOnDelete();
            $table->foreignId('penanggung_jawab_baru_id')->nullable()->constrained('aset_tik_penanggung_jawabs')->nullOnDelete();
            $table->decimal('biaya', 15, 2)->nullable();
            $table->string('kondisi_akhir')->nullable();
            $table->string('dokumen')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('aset_tik_riwayats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_tik_id')->constrained('aset_tiks')->cascadeOnDelete();
            $table->foreignId('transaksi_id')->nullable()->constrained('aset_tik_transaksis')->nullOnDelete();
            $table->string('aktivitas');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aset_tik_riwayats');
        Schema::dropIfExists('aset_tik_transaksis');
    }
};
