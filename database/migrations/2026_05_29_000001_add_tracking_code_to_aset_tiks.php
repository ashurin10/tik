<?php

use App\Models\AsetTik;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aset_tiks', function (Blueprint $table) {
            $table->string('tracking_code')->nullable()->unique()->after('kode');
        });

        AsetTik::query()->get()->each(function (AsetTik $aset) {
            $aset->forceFill([
                'tracking_code' => $this->trackingCode($aset->kode),
            ])->save();
        });
    }

    public function down(): void
    {
        Schema::table('aset_tiks', function (Blueprint $table) {
            $table->dropColumn('tracking_code');
        });
    }

    private function trackingCode(string $kode): string
    {
        return 'TIK-' . Str::of($kode)->upper()->replaceMatches('/[^A-Z0-9]+/', '-')->trim('-');
    }
};
