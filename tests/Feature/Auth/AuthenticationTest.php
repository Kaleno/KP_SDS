<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_login_screen_renders_masjid_branding(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Taman Pendidikan Al-Qur\'an', false)
            ->assertSee('Masjid Al Ihsan')
            ->assertSee('Pembelajaran Iqro, Alquran dan Hafalan')
            ->assertSee('Absensi sesi harian Senin–Jumat untuk semua santri aktif.')
            ->assertSee('Progress bacaan & hafalan disesuaikan jenis setoran.', false)
            ->assertSee('Barangsiapa menempuh suatu jalan untuk mencari ilmu, maka Allah mudahkan untuknya jalan menuju surga.')
            ->assertSee('(HR. Muslim). Sebuah ruang digital untuk mendukung perjalanan mulia para santri.')
            ->assertSee('images/logo-masjid-al-ihsan.png', false)
            ->assertDontSee('Pesantren / Madrasah')
            ->assertDontSee('Monitoring Hafalan')
            ->assertDontSee('Absensi, setoran, dan jadwal santri')
            ->assertDontSee('Penilaian Lulus / Mengulang untuk Alquran dan Iqro.')
            ->assertDontSee('Progress 30 juz dihitung dari ayat yang sudah lancar.')
            ->assertDontSee('Progress 30 juz dihitung dari ayat unik yang sudah lancar.')
            ->assertDontSee('Dirancang untuk pesantren dan madrasah');
    }

    public function test_login_screen_renders_password_visibility_toggle(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('ui-password', false)
            ->assertSee('ui-password-toggle', false)
            ->assertSee('Tampilkan kata sandi', false)
            ->assertSee('Sembunyikan kata sandi', false);
    }

    public function test_users_can_authenticate_using_username(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login' => $user->username,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_using_email(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', [
            'login' => $user->username,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'login' => $user->username,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
