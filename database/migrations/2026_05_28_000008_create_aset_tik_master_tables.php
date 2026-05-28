<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aset_tik_kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('aset_tik_lokasis', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('gedung')->nullable();
            $table->string('ruangan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('aset_tik_penanggung_jawabs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('unit_kerja')->nullable();
            $table->timestamps();
        });

        Schema::create('aset_tik_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('kontak')->nullable();
            $table->string('pic')->nullable();
            $table->timestamps();
        });

        Schema::create('aset_tiks', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->foreignId('kategori_id')->nullable()->constrained('aset_tik_kategoris')->nullOnDelete();
            $table->string('merk')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->year('tahun_perolehan')->nullable();
            $table->decimal('nilai', 15, 2)->nullable();
            $table->string('kondisi')->default('Baik');
            $table->string('status')->default('Aktif');
            $table->foreignId('lokasi_id')->nullable()->constrained('aset_tik_lokasis')->nullOnDelete();
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('aset_tik_penanggung_jawabs')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aset_tiks');
        Schema::dropIfExists('aset_tik_vendors');
        Schema::dropIfExists('aset_tik_penanggung_jawabs');
        Schema::dropIfExists('aset_tik_lokasis');
        Schema::dropIfExists('aset_tik_kategoris');
    }
};
