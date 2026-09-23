<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KetuaMasterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_cannot_open_ketua_master(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::SuperAdmin);

        $this->actingAs($admin)
            ->get(route('ketua.santri.index'))
            ->assertForbidden();
    }

    public function test_removed_academic_year_routes_return_not_found(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)->get('/ketua/academic-years')->assertNotFound();
        $this->actingAs($ketua)->post('/ketua/academic-years', [
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => '1',
        ])->assertNotFound();
    }

    public function test_ketua_can_create_ketua_pengajar_and_pengajar(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)->post(route('ketua.ustaz.store'), [
            'teaching_role' => Role::KetuaPengajar,
            'name' => 'Ketua Pengajar Satu',
            'username' => 'kp1',
            'email' => null,
            'phone' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('ketua.ustaz.index'));

        $this->actingAs($ketua)->post(route('ketua.ustaz.store'), [
            'teaching_role' => Role::Pengajar,
            'name' => 'Pengajar Dua',
            'username' => 'pjr1',
            'email' => null,
            'phone' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('ketua.ustaz.index'));

        $this->assertTrue(User::query()->where('username', 'kp1')->first()->hasRole(Role::KetuaPengajar));
        $this->assertTrue(User::query()->where('username', 'pjr1')->first()->hasRole(Role::Pengajar));
    }

    public function test_guest_is_redirected_from_setup_wizard(): void
    {
        $this->get(route('ketua.setup.create'))
            ->assertRedirect(route('login'));
    }

    public function test_ustaz_cannot_open_setup_wizard(): void
    {
        $ustaz = $this->userWithRole(Role::KetuaPengajar);

        $this->actingAs($ustaz)
            ->get(route('ketua.setup.create'))
            ->assertForbidden();
    }

    public function test_setup_wizard_redirects_to_santri_index(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('ketua.setup.create'))
            ->assertRedirect(route('ketua.santri.index'));
    }

    private function userWithRole(string $role, array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        $user->assignRole($role);

        return $user;
    }
}
