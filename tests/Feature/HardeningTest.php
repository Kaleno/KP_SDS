<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_send_security_headers(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_ketua_cannot_delete_their_account(): void
    {
        $this->seed(RoleSeeder::class);
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('Hapus akun');

        $this->actingAs($ketua)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ])
            ->assertForbidden();

        $this->assertModelExists($ketua);
    }

    public function test_super_admin_cannot_delete_their_account(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole(Role::SuperAdmin);

        $this->actingAs($admin)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ])
            ->assertForbidden();

        $this->assertModelExists($admin);
    }

    public function test_public_registration_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post(route('daftar.store'), [])->assertRedirect();
        }

        $this->post(route('daftar.store'), [])->assertStatus(429);
    }
}
