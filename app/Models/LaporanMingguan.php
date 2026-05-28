<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use App\Traits\HasHashid;

class LaporanMingguan extends Model
{
    use HasFactory, HasHashid;
    
    protected $guarded = [];
    protected $appends = ['hashid'];

    protected static function booted(): void
    {
        static::creating(function (LaporanMingguan $laporan) {
            $userId = auth()->id();
            if (!$userId) return;

            if (self::hasAuditColumn('created_by') && empty($laporan->created_by)) {
                $laporan->created_by = $userId;
            }

            if (self::hasAuditColumn('updated_by') && empty($laporan->updated_by)) {
                $laporan->updated_by = $userId;
            }
        });

        static::updating(function (LaporanMingguan $laporan) {
            $userId = auth()->id();
            if ($userId && self::hasAuditColumn('updated_by')) {
                $laporan->updated_by = $userId;
            }
        });
    }

    private static function hasAuditColumn(string $column): bool
    {
        static $columns = [];

        return $columns[$column] ??= Schema::hasColumn('laporan_mingguans', $column);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
