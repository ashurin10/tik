<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Service untuk menghasilkan kode/nomor transaksi unik dengan pengecekan collision.
 */
class UniqueCodeService
{
    /**
     * Generate nomor transaksi unik dengan prefix dan tanggal.
     *
     * @param string $prefix Contoh: 'TM', 'OUT', 'MUT'
     * @param string $table Nama tabel untuk pengecekan duplikat
     * @param string $column Nama kolom yang menyimpan nomor transaksi
     * @param int $randomLength Panjang random suffix (default: 5)
     * @return string
     */
    public static function generate(
        string $prefix,
        string $table,
        string $column = 'no_transaksi',
        int $randomLength = 5
    ): string {
        $date = date('Ymd');
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $suffix = strtoupper(Str::random($randomLength));
            $code = "{$prefix}-{$date}-{$suffix}";
            $attempt++;

            $exists = \DB::table($table)->where($column, $code)->exists();
        } while ($exists && $attempt < $maxAttempts);

        if ($exists) {
            // Fallback: tambahkan microtime jika masih collision
            $suffix = strtoupper(Str::random($randomLength)) . substr((string) microtime(true), -4);
            $code = "{$prefix}-{$date}-{$suffix}";
        }

        return $code;
    }

    /**
     * Generate kode aset unik dengan format INV-YYYY-XXX.
     * Menggunakan database lock untuk mencegah race condition.
     *
     * @return string
     */
    public static function generateAssetCode(): string
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";

        return \DB::transaction(function () use ($prefix, $year) {
            // Lock rows yang cocok pola untuk mencegah race condition
            $lastAsset = \App\Models\AsetTik::where('kode_aset', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('kode_aset');

            if ($lastAsset) {
                // Ambil bagian numerik setelah prefix terakhir
                $numericPart = (int) substr($lastAsset, strlen($prefix));
                $nextSequence = $numericPart + 1;
            } else {
                $nextSequence = 1;
            }

            $code = $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);

            // Double-check unik (safety net)
            while (\App\Models\AsetTik::where('kode_aset', $code)->exists()) {
                $nextSequence++;
                $code = $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
            }

            return $code;
        });
    }
}
