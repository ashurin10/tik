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
        Schema::create('laporan_aktivitas_kerjas', function (Blueprint $table) {
            $table->id();
            $table->string('pic');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('uraian_kegiatan');
            $table->string('keterangan')->default('tj');
            $table->unsignedBigInteger('laporan_mingguan_id')->nullable();
            
            // Foreign key as cascading delete if LaporanMingguan is deleted (optional but good practice)
            $table->foreign('laporan_mingguan_id')
                  ->references('id')->on('laporan_mingguans')
                  ->onDelete('cascade');
                  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_aktivitas_kerjas');
    }
};
