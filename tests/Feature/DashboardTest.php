<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SetoranStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\QuranSurah;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->travelTo('2026-09-23 10:00:00');
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_layout_renders_masjid_branding(): void
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Masjid Al Ihsan')
            ->assertSee('images/logo-masjid-al-ihsan.png', false)
            ->assertDontSee('Monitoring Hafalan');
    }

    public function test_ketua_layout_renders_logout_inside_the_mobile_menu(): void
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Menu')
            ->assertSee('Profil')
            ->assertSee('Keluar')
            ->assertSee('data-nav="mobile-logout"', false);
    }

    public function test_ketua_layout_locks_the_sidebar_and_scrolls_the_content_pane(): void
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('lg:h-screen lg:overflow-hidden', false)
            ->assertSee('ui-content-scroll', false)
            ->assertSee('x-ref="sidebarNav"', false);
    }

    public function test_ketua_dashboard_shows_operational_today_summary(): void
    {
        $fx = $this->opsFixture();
        $this->seedTodayActivity($fx);

        $html = $this->actingAs($fx['ketua'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Absensi hari ini')
            ->assertSee('Sesi terbuka')
            ->assertSee('Isi absensi')
            ->assertSee('Perlu perhatian')
            ->assertSee('Yusuf Maulana')
            ->assertSee('Hasan Basri')
            ->assertSee('Al-Fatihah')
            ->assertSee('Belum setor hari ini')
            ->assertSee('Setoran perlu diulang')
            ->assertSee('Santri aktif')
            ->assertSee('Setoran hari ini')
            ->assertSee('Alfa hari ini')
            ->assertSee('Sesi hari ini')
            ->assertSee('2/3 sudah setor')
            ->assertSee('Ringkasan SPP')
            ->assertSee('Minggu ini')
            ->assertSee('Rekap minggu ini')
            ->assertDontSee('Kelas aktif')
            ->assertDontSee('Halaqah Tahfidz A')
            ->assertDontSee('Jadwal hari ini')
            ->assertDontSee('Kehadiran hari ini')
            ->assertDontSee('Setoran terkini')
            ->assertDontSee('Alfa minggu ini')
            ->assertDontSee('Input setoran')
            ->assertDontSee('Buka sesi')
            ->assertDontSee('Belum dibuka')
            ->assertDontSee('Kelas belum siap dipakai')
            ->assertDontSee('Buat kelas')
            ->assertDontSee('07:00')
            ->assertDontSee('Masjid Utama')
            ->getContent();

        $slotPos = strpos($html, 'Absensi hari ini');
        $attentionPos = strpos($html, 'Perlu perhatian');
        $sppPos = strpos($html, 'Ringkasan SPP');
        $weekPos = strpos($html, 'Minggu ini');

        $this->assertNotFalse($slotPos);
        $this->assertNotFalse($attentionPos);
        $this->assertNotFalse($sppPos);
        $this->assertNotFalse($weekPos);
        $this->assertTrue($slotPos < $attentionPos);
        $this->assertTrue($attentionPos < $sppPos);
        $this->assertTrue($sppPos < $weekPos);
    }

    public function test_ustaz_dashboard_shows_shared_ops_activity(): void
    {
        $fx = $this->opsFixture();
        $this->seedTodayActivity($fx);

        $this->actingAs($fx['ustaz'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Absensi hari ini')
            ->assertSee('Perlu perhatian')
            ->assertSee('Santri aktif')
            ->assertSee('Sesi hari ini')
            ->assertSee('Setoran hari ini')
            ->assertSee('Alfa hari ini')
            ->assertSee('Yusuf Maulana')
            ->assertSee('Al-Fatihah')
            ->assertSee('Hasan Basri')
            ->assertDontSee('Ringkasan SPP')
            ->assertDontSee('Kelas Anda')
            ->assertDontSee('Halaqah Tahfidz A')
            ->assertDontSee('Anggota aktif')
            ->assertDontSee('07:00')
            ->assertDontSee('Masjid Utama');
    }

    public function test_ketua_without_santri_sees_readiness_banner_without_buat_kelas(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum siap dipakai')
            ->assertSee('Akun pengajar')
            ->assertSee('Santri aktif')
            ->assertSee('Tambah santri')
            ->assertDontSee('Buat kelas')
            ->assertDontSee('Kelas belum siap dipakai')
            ->assertSee('Minggu ini');
    }

    public function test_another_pengajar_sees_same_ops_activity_on_dashboard(): void
    {
        $fx = $this->opsFixture();
        $this->seedTodayActivity($fx);
        $outsider = $this->userWithRole(Role::Pengajar, ['username' => 'ustaz2']);

        $this->actingAs($outsider)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Yusuf Maulana')
            ->assertSee('Al-Fatihah')
            ->assertSee('Absensi hari ini')
            ->assertDontSee('Halaqah Tahfidz A');
    }

    /**
     * @return array{ketua: User, ustaz: User, santri: list<SantriProfile>}
     */
    private function opsFixture(): array
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole(Role::KetuaPengajar, ['username' => 'ustaz1', 'name' => 'Ustaz Ahmad']);

        $santri = [];
        foreach (['2026001' => 'Ahmad Fauzi', '2026002' => 'Hasan Basri', '2026003' => 'Yusuf Maulana'] as $nis => $name) {
            $santri[] = $this->makeSantri($nis, $name);
        }

        return compact('ketua', 'ustaz', 'santri');
    }

    /**
     * @param  array{ustaz: User, santri: list<SantriProfile>}  $fx
     */
    private function seedTodayActivity(array $fx): void
    {
        QuranSurah::query()->create([
            'id' => 1,
            'name_id' => 'Al-Fatihah',
            'name_ar' => 'الفاتحة',
            'ayah_count' => 7,
        ]);

        $session = AttendanceSession::query()->create([
            'session_date' => now()->toDateString(),
            'opened_by_user_id' => $fx['ustaz']->id,
        ]);

        $statuses = [AttendanceStatus::Hadir, AttendanceStatus::Izin, AttendanceStatus::Alfa];
        foreach ($fx['santri'] as $index => $santri) {
            Attendance::query()->create([
                'attendance_session_id' => $session->id,
                'santri_id' => $santri->id,
                'status' => $statuses[$index],
            ]);
        }

        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri'][0]->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lulus,
        ]);
        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri'][1]->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Mengulang,
        ]);
    }

    private function userWithRole(string $role, array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        $user->assignRole($role);

        return $user;
    }

    private function makeSantri(string $nis, string $name): SantriProfile
    {
        $user = User::factory()->create([
            'name' => $name,
            'username' => $nis,
            'email' => $nis.'@santri.test',
        ]);
        $user->assignRole(Role::Santri);

        return SantriProfile::query()->create([
            'user_id' => $user->id,
            'nis' => $nis,
            'gender' => Gender::LakiLaki,
            'status' => SantriStatus::Aktif,
        ]);
    }
}
