<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KetuaKelasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_gets_not_found_for_removed_kelas_paths(): void
    {
        $this->get('/ketua/halaqah/create')->assertNotFound();
        $this->get('/ketua/halaqah')->assertNotFound();
    }

    public function test_removed_kelas_and_academic_year_routes_return_not_found(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)->get('/ketua/halaqah')->assertNotFound();
        $this->actingAs($ketua)->get('/ketua/halaqah/create')->assertNotFound();
        $this->actingAs($ketua)->post('/ketua/halaqah', [])->assertNotFound();
        $this->actingAs($ketua)->get('/ketua/academic-years')->assertNotFound();
        $this->actingAs($ketua)->post('/ketua/academic-years', [])->assertNotFound();
    }

    public function test_setup_wizard_redirects_to_santri_index(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('ketua.setup.create'))
            ->assertRedirect(route('ketua.santri.index'));
    }

    public function test_ustaz_cannot_open_setup_wizard(): void
    {
        $ustaz = $this->userWithRole(Role::KetuaPengajar);

        $this->actingAs($ustaz)
            ->get(route('ketua.setup.create'))
            ->assertForbidden();
    }

    private function userWithRole(string $role, array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        $user->assignRole($role);

        return $user;
    }
}
