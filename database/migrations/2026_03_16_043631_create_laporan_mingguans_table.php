<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_mingguans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_kegiatan');
            $table->string('lokasi');
            $table->text('hasil_deskripsi')->nullable();
            $table->string('prioritas'); // Tinggi, Sedang, Rendah
            $table->string('pic');
            $table->string('status'); // Selesai, Berjalan, Tertunda
            $table->text('keterangan_tindak_lanjut')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_mingguans');
    }
};
