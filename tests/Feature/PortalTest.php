<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_and_replace_a_portal_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['peran' => 'admin']);
        $data = ['title' => 'Upload Aman', 'url' => 'laporan-mingguan.index'];

        $this->actingAs($admin)->post(route('services.store'), $data + [
            'image' => UploadedFile::fake()->image('logo.png'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

        $service = Service::where('title', 'Upload Aman')->firstOrFail();
        $oldImage = $service->image;
        Storage::disk('public')->assertExists($oldImage);

        $this->actingAs($admin)->put(route('services.update', $service), $data + [
            'image' => UploadedFile::fake()->image('pengganti.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($service->fresh()->image);
    }

    public function test_portal_rejects_scripts_and_images_with_executable_extensions(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['peran' => 'admin']);
        $service = Service::create(['title' => 'Gambar Lama', 'url' => 'laporan-mingguan.index']);
        $png = UploadedFile::fake()->image('logo.png')->getContent();
        $data = ['title' => 'Upload Ditolak', 'url' => 'laporan-mingguan.index'];

        foreach (['shell.php', 'shell.phtml', 'shell.phar', 'shell.PNG.php'] as $filename) {
            $this->actingAs($admin)->post(route('services.store'), $data + [
                'image' => UploadedFile::fake()->createWithContent($filename, $png),
            ])->assertSessionHasErrors('image');

            $this->actingAs($admin)->put(route('services.update', $service), $data + [
                'image' => UploadedFile::fake()->createWithContent($filename, $png),
            ])->assertSessionHasErrors('image');
        }

        $script = UploadedFile::fake()->createWithContent('shell.png', '<?php echo "not an image";');
        $this->actingAs($admin)->post(route('services.store'), $data + [
            'image' => new UploadedFile($script->getRealPath(), 'shell.png', 'image/png', null, true),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('services', ['title' => 'Upload Ditolak']);
        $this->assertNull($service->fresh()->image);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

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
            ->assertDontSee(url('/database-backup'), false)
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
            ->assertDontSee('Backup Database')
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
            ->assertDontSee('Backup Database')
            ->assertDontSee('URL Shortener')
            ->assertDontSee('Data Laporan');
    }
}
