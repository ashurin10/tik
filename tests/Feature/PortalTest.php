<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_portal(): void
    {
        $user = User::factory()->create(['peran' => 'user']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Portal Sistem TIK')
            ->assertDontSee('Panel Admin')
            ->assertDontSee('Manajemen Pengguna')
            ->assertDontSee('Manajemen Menu')
            ->assertDontSee('Backup Database')
            ->assertDontSee('Tambah Sistem');
    }

    public function test_admin_can_manage_portal_services(): void
    {
        $admin = User::factory()->create(['peran' => 'admin']);

        $this->actingAs($admin)
            ->post(route('services.store'), [
                'title' => 'Sistem Baru',
                'category' => 'Pengujian',
                'url' => 'laporan-mingguan.index',
                'icon_class' => 'fas fa-check',
                'description' => 'Sistem untuk pengujian portal.',
                'order' => 99,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('services', [
            'title' => 'Sistem Baru',
            'url' => 'laporan-mingguan.index',
        ]);

        $service = Service::where('title', 'Sistem Baru')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('services.destroy', $service))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('services', [
            'title' => 'Sistem Baru',
        ]);
    }

    public function test_portal_cards_link_to_their_features(): void
    {
        $user = User::factory()->create(['peran' => 'user']);
        $admin = User::factory()->create(['peran' => 'admin']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('laporan-mingguan.index'), false)
            ->assertSee(route('urls.index'), false)
            ->assertDontSee(route('users.index'), false)
            ->assertDontSee(route('menus.index'), false)
            ->assertDontSee(route('database-backup.index'), false)
            ->assertDontSee('href="http://localhost/aset"', false);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Panel Admin')
            ->assertSee(route('users.index'), false)
            ->assertDontSee('Manajemen Pengguna')
            ->assertDontSee('Manajemen Menu')
            ->assertDontSee('Backup Database');
    }

    public function test_application_layout_only_shows_selected_application_menu(): void
    {
        $admin = User::factory()->create(['peran' => 'admin']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Manajemen Pengguna')
            ->assertSee('Manajemen Menu')
            ->assertSee('Backup Database')
            ->assertSee(route('dashboard'), false)
            ->assertDontSee('URL Shortener')
            ->assertDontSee('Data Laporan');

        $this->actingAs($admin)
            ->get(route('laporan-mingguan.index'))
            ->assertOk()
            ->assertSee('Laporan Mingguan')
            ->assertSee('Data Laporan')
            ->assertDontSee('Manajemen Pengguna')
            ->assertDontSee('Backup Database');
    }

    public function test_asset_management_portal_card_opens_asset_menu_group(): void
    {
        $user = User::factory()->create(['peran' => 'user']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Manajemen Aset TIK')
            ->assertSee(route('aset-tik.dashboard'), false);

        $this->actingAs($user)
            ->get(route('aset-tik.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Aset')
            ->assertSee('Master Data')
            ->assertSee('Data Aset TIK')
            ->assertSee('Transaksi Aset')
            ->assertSee('Maintenance Aset')
            ->assertSee('Laporan Aset')
            ->assertDontSee('URL Shortener')
            ->assertDontSee('Panel Admin')
            ->assertDontSee('Manajemen Pengguna');
    }

    public function test_superadmin_can_open_admin_portal_menu_group(): void
    {
        $superadmin = User::factory()->create(['peran' => 'superadmin']);

        $this->actingAs($superadmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Panel Admin')
            ->assertSee(route('users.index'), false);

        $this->actingAs($superadmin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Manajemen Pengguna')
            ->assertSee('Manajemen Menu')
            ->assertSee('Backup Database')
            ->assertDontSee('URL Shortener')
            ->assertDontSee('Data Laporan');
    }
}
