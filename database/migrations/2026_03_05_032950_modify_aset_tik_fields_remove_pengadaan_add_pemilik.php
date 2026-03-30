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
        Schema::table('aset_tik', function (Blueprint $table) {
            $table->dropColumn(['tahun_pengadaan', 'garansi_sampai']);
            $table->string('pemilik_aset')->nullable()->after('penanggung_jawab');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aset_tik', function (Blueprint $table) {
            $table->dropColumn('pemilik_aset');
            $table->year('tahun_pengadaan')->nullable();
            $table->date('garansi_sampai')->nullable();
        });
    }
};
