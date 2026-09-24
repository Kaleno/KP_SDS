<?php

namespace Tests\Feature;

use App\Models\QuranJuz;
use App\Models\QuranSurah;
use App\Models\User;
use App\Support\Role;
use Database\Seeders\QuranSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_can_view_ketua_accounts(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::SuperAdmin);

        $ketua = User::factory()->create([
            'name' => 'Mudir Utama',
            'place_name' => 'Masjid Al Ihsan',
            'address' => 'Jl. Masjid No. 1',
            'phone' => '081234567890',
        ]);
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($admin)
            ->get(route('super-admin.ketua.index'))
            ->assertOk()
            ->assertSee('Daftar Akun')
            ->assertSee('Tambah ketua')
            ->assertSee(route('super-admin.ketua.create'), false)
            ->assertSee('Mudir Utama')
            ->assertSee('Masjid Al Ihsan')
            ->assertSee('Jl. Masjid No. 1')
            ->assertSee('081234567890');
    }

    public function test_super_admin_can_open_create_ketua_form(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::SuperAdmin);

        $this->actingAs($admin)
            ->get(route('super-admin.ketua.create'))
            ->assertOk()
            ->assertSee('Nama tempat')
            ->assertSee('Alamat')
            ->assertSee('Telepon');
    }

    public function test_super_admin_can_create_ketua_with_place_details(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::SuperAdmin);

        $this->actingAs($admin)
            ->post(route('super-admin.ketua.store'), [
                'name' => 'Ahmad Ketua',
                'place_name' => 'TPQ Al Huda',
                'address' => 'Jl. Melati 8',
                'phone' => '081111222333',
                'username' => 'ahmadketua',
                'email' => 'ahmad.ketua@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'is_active' => '0',
            ])
            ->assertRedirect(route('super-admin.ketua.index'))
            ->assertSessionHas('status', 'Akun Ketua berhasil dibuat.');

        $ketua = User::query()->where('username', 'ahmadketua')->first();

        $this->assertNotNull($ketua);
        $this->assertSame('TPQ Al Huda', $ketua->place_name);
        $this->assertSame('Jl. Melati 8', $ketua->address);
        $this->assertSame('081111222333', $ketua->phone);
        $this->assertTrue($ketua->is_active);
        $this->assertTrue($ketua->hasRole(Role::Ketua));
    }

    public function test_create_ketua_requires_place_name_address_and_phone(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::SuperAdmin);

        $this->actingAs($admin)
            ->from(route('super-admin.ketua.create'))
            ->post(route('super-admin.ketua.store'), [
                'name' => 'Tanpa Tempat',
                'username' => 'tanpatempat',
                'email' => 'tanpa@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('super-admin.ketua.create'))
            ->assertSessionHasErrors([
                'place_name' => 'Nama tempat wajib diisi.',
                'address' => 'Alamat wajib diisi.',
                'phone' => 'Telepon wajib diisi.',
            ]);

        $this->assertDatabaseMissing('users', ['username' => 'tanpatempat']);
    }

    public function test_ketua_cannot_create_another_ketua_account(): void
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('super-admin.ketua.create'))
            ->assertForbidden();

        $this->actingAs($ketua)
            ->post(route('super-admin.ketua.store'), [
                'name' => 'Ketua Lain',
                'place_name' => 'Tempat Lain',
                'address' => 'Jl. Lain',
                'phone' => '0800000000',
                'username' => 'ketualain',
                'email' => 'lain@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['username' => 'ketualain']);
    }

    public function test_guest_is_redirected_from_ketua_account_list(): void
    {
        $this->get(route('super-admin.ketua.index'))
            ->assertRedirect(route('login'));
    }

    public function test_ketua_list_escapes_place_and_contact_fields(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::SuperAdmin);

        $ketua = User::factory()->create([
            'name' => "Ahmad <script>alert('x')</script>",
            'place_name' => "Masjid <script>alert('y')</script>",
            'address' => "Jl. <script>alert('z')</script>",
            'phone' => '08<script>1',
        ]);
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($admin)
            ->get(route('super-admin.ketua.index'))
            ->assertOk()
            ->assertDontSee("<script>alert('x')</script>", false)
            ->assertDontSee("<script>alert('y')</script>", false)
            ->assertDontSee("<script>alert('z')</script>", false)
            ->assertSee("Ahmad <script>alert('x')</script>")
            ->assertSee("Masjid <script>alert('y')</script>")
            ->assertSee("Jl. <script>alert('z')</script>");
    }

    public function test_ketua_cannot_access_super_admin_menu(): void
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('super-admin.ketua.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_toggle_ketua_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::SuperAdmin);

        $ketua = User::factory()->create(['is_active' => true]);
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($admin)
            ->patch(route('super-admin.ketua.toggle', $ketua))
            ->assertRedirect(route('super-admin.ketua.index'));

        $this->assertFalse($ketua->fresh()->is_active);
    }

    public function test_quran_seeder_loads_surah_and_juz(): void
    {
        $this->seed(QuranSeeder::class);

        $this->assertSame(114, QuranSurah::query()->count());
        $this->assertSame(30, QuranJuz::query()->count());
        $this->assertSame(6236, (int) QuranSurah::query()->sum('ayah_count'));
    }
}
