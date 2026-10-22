<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuAccess;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DatabaseBackupRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_backup_endpoints_and_command_are_removed(): void
    {
        $admin = User::factory()->create(['peran' => 'admin']);

        $this->assertFalse(Route::has('database-backup.index'));
        $this->assertFalse(Route::has('database-backup.store'));
        $this->assertArrayNotHasKey('backup:database', Artisan::all());

        $this->actingAs($admin)->get('/database-backup')->assertNotFound();
        $this->actingAs($admin)->post('/database-backup')->assertNotFound();
    }

    public function test_fresh_database_has_no_backup_menu_or_service(): void
    {
        $this->assertDatabaseMissing('menus', ['url' => 'database-backup.index']);
        $this->assertDatabaseMissing('services', ['url' => 'database-backup.index']);
        $this->assertDatabaseHas('services', [
            'url' => 'users.index',
            'description' => 'Kelola pengguna dan menu aplikasi.',
        ]);
    }

    public function test_migration_removes_legacy_backup_entries_and_preserves_other_menus(): void
    {
        $menu = Menu::create([
            'name' => 'Backup Database',
            'url' => 'database-backup.index',
        ]);
        MenuAccess::create(['menu_id' => $menu->id, 'role' => 'admin']);
        Service::create(['title' => 'Backup Database', 'url' => 'database-backup.index']);
        Service::where('url', 'users.index')->update([
            'description' => 'Kelola pengguna, menu aplikasi, dan backup database.',
        ]);
        $usersMenu = Menu::where('url', 'users.index')->firstOrFail();
        $usersMenuRoles = $usersMenu->roles()->count();

        $migration = require database_path('migrations/2026_10_21_000000_remove_database_backup_feature.php');
        $migration->up();
        $migration->up();

        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
        $this->assertDatabaseMissing('menu_access', ['menu_id' => $menu->id]);
        $this->assertDatabaseMissing('services', ['url' => 'database-backup.index']);
        $this->assertDatabaseHas('menus', ['id' => $usersMenu->id]);
        $this->assertSame($usersMenuRoles, $usersMenu->roles()->count());
        $this->assertDatabaseHas('services', [
            'url' => 'users.index',
            'description' => 'Kelola pengguna dan menu aplikasi.',
        ]);
    }
}
