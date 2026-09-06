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

        $ketua = User::factory()->create(['name' => 'Mudir Utama']);
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($admin)
            ->get(route('super-admin.ketua.index'))
            ->assertOk()
            ->assertSee('Mudir Utama');
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
