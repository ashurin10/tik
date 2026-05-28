<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackupController extends Controller
{
    private string $backupDirectory = 'app/private/backups/database';

    public function index()
    {
        abort_unless(auth()->user()?->isAdminOrSuperAdmin(), 403);

        return view('database-backup.index', [
            'driver' => config('database.default'),
            'databasePath' => $this->sqliteDatabasePath(),
            'backups' => $this->listBackups(),
            'isSupported' => $this->isSqliteBackupSupported(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->isAdminOrSuperAdmin(), 403);
        abort_unless($this->isSqliteBackupSupported(), 422, 'Backup otomatis saat ini hanya mendukung database SQLite.');

        $backupPath = $this->createSqliteBackup();

        return response()->download($backupPath, basename($backupPath), [
            'Content-Type' => 'application/vnd.sqlite3',
        ]);
    }

    private function isSqliteBackupSupported(): bool
    {
        $path = $this->sqliteDatabasePath();

        return config('database.default') === 'sqlite'
            && $path !== null
            && $path !== ':memory:'
            && File::exists($path);
    }

    private function sqliteDatabasePath(): ?string
    {
        if (config('database.default') !== 'sqlite') {
            return null;
        }

        return config('database.connections.sqlite.database');
    }

    private function createSqliteBackup(): string
    {
        $backupDir = storage_path($this->backupDirectory);
        File::ensureDirectoryExists($backupDir);

        $fileName = 'database-backup-' . now()->format('Ymd-His') . '.sqlite';
        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

        try {
            $quotedPath = DB::getPdo()->quote($backupPath);
            DB::statement("VACUUM INTO {$quotedPath}");
        } catch (\Throwable $e) {
            File::copy($this->sqliteDatabasePath(), $backupPath);
        }

        return $backupPath;
    }

    private function listBackups(): array
    {
        $backupDir = storage_path($this->backupDirectory);
        if (!File::isDirectory($backupDir)) {
            return [];
        }

        return collect(File::files($backupDir))
            ->filter(fn($file) => $file->getExtension() === 'sqlite')
            ->sortByDesc(fn($file) => $file->getMTime())
            ->map(fn($file) => [
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'created_at' => date('d/m/Y H:i:s', $file->getMTime()),
            ])
            ->values()
            ->all();
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
