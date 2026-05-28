<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup copy of the SQLite database (if applicable)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $driver = config('database.default');
        if ($driver !== 'sqlite') {
            $this->error('Backup command currently only supports SQLite driver.');
            return 1;
        }

        $dbPath = config('database.connections.sqlite.database');
        if ($dbPath === ':memory:' || !File::exists($dbPath)) {
            $this->error('SQLite database file not found or is in-memory.');
            return 1;
        }

        $backupDir = storage_path('app/private/backups/database');
        File::ensureDirectoryExists($backupDir);

        $fileName = 'database-backup-' . now()->format('Ymd-His') . '.sqlite';
        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

        try {
            $quotedPath = DB::getPdo()->quote($backupPath);
            DB::statement("VACUUM INTO {$quotedPath}");
        } catch (\Throwable $e) {
            File::copy($dbPath, $backupPath);
        }

        $this->info('Database backup created: ' . $backupPath);
        return 0;
    }
}
