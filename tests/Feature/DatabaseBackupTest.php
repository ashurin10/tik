<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_database_backup_page(): void
    {
        $admin = User::factory()->create(['peran' => 'admin']);

        $this->actingAs($admin)
            ->get(route('database-backup.index'))
            ->assertOk()
            ->assertSee('Backup Database');
    }

    public function test_non_admin_cannot_view_database_backup_page(): void
    {
        $user = User::factory()->create(['peran' => 'user']);

        $this->actingAs($user)
            ->get(route('database-backup.index'))
            ->assertForbidden();
    }

    public function test_admin_can_download_sqlite_backup_when_database_is_file_based(): void
    {
        $databasePath = database_path('test-backup.sqlite');
        File::put($databasePath, '');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $databasePath,
        ]);

        $admin = User::factory()->create(['peran' => 'admin']);

        $response = $this->actingAs($admin)->post(route('database-backup.store'));

        $response->assertOk();
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));

        File::delete($databasePath);
    }
}
