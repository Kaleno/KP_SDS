<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\FinanceSource;
use App\Enums\FinanceType;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SetoranStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\FinanceEntry;
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

    public function test_ketua_dashboard_shows_santri_spp_week_and_cash(): void
    {
        $fx = $this->opsFixture();
        $this->seedTodayActivity($fx);

        $this->makeSantri('2026004', 'Aisyah', Gender::Perempuan);
        $this->makeSantri('2026005', 'Fatimah', Gender::Perempuan, SantriStatus::Lulus);
        $this->makeSantri('2026006', 'Umar', Gender::LakiLaki, SantriStatus::Cuti);

        FinanceEntry::query()->create([
            'type' => FinanceType::Pemasukan,
            'source' => FinanceSource::Manual,
            'amount' => 100_000,
            'entry_date' => now()->subMonth()->toDateString(),
            'created_by' => $fx['ketua']->id,
        ]);
        FinanceEntry::query()->create([
            'type' => FinanceType::Pemasukan,
            'source' => FinanceSource::Manual,
            'amount' => 50_000,
            'entry_date' => now()->toDateString(),
            'created_by' => $fx['ketua']->id,
        ]);
        FinanceEntry::query()->create([
            'type' => FinanceType::Pengeluaran,
            'source' => FinanceSource::Manual,
            'amount' => 40_000,
            'entry_date' => now()->toDateString(),
            'created_by' => $fx['ketua']->id,
        ]);

        $this->actingAs($fx['ketua'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder([
                'Santri aktif',
                'Perempuan',
                'Laki-laki',
                'Lulus',
                'Ringkasan SPP',
                'Minggu ini',
                'Rekap minggu ini',
                'Keuangan',
                'Saldo',
                'Pemasukan',
                'Pengeluaran',
            ])
            ->assertSeeTextInOrder(['4', 'Santri aktif', '1', 'Perempuan', '3', 'Laki-laki', '1', 'Lulus'])
            ->assertSee('150.000')
            ->assertSee('40.000')
            ->assertSee('110.000')
            ->assertSee('Hadir')
            ->assertDontSee('Belum setor hari ini')
            ->assertDontSee('Setoran hari ini')
            ->assertDontSee('Setoran perlu diulang')
            ->assertDontSee('Absensi hari ini')
            ->assertDontSee('Isi absensi')
            ->assertDontSee('Perlu perhatian')
            ->assertDontSee('Alfa hari ini')
            ->assertDontSee('Sesi hari ini')
            ->assertDontSee('Yusuf Maulana')
            ->assertDontSee('Hasan Basri')
            ->assertDontSee('Kelas aktif')
            ->assertDontSee('Halaqah Tahfidz A');
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
            ->assertSeeInOrder(['Setoran perlu diulang', 'Belum setor hari ini'])
            ->assertSee('data-queue="setoran-ulang"', false)
            ->assertSee('data-queue="belum-setor"', false)
            ->assertSee('lg:grid-cols-2', false)
            ->assertSee('max-h-64 space-y-3 overflow-y-auto', false)
            ->assertDontSee('Ringkasan SPP')
            ->assertDontSee('Kas DKM')
            ->assertDontSee('Keuangan')
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
            ->assertDontSee('Isi absensi')
            ->assertDontSee('Belum setor hari ini')
            ->assertSee('Minggu ini')
            ->assertSee('Keuangan');

        $this->assertSame(0, AttendanceSession::query()->count());
    }

    public function test_pengajar_dashboard_lists_every_repeat_and_pending_setoran(): void
    {
        $fx = $this->opsFixture();
        $this->seedTodayActivity($fx);

        QuranSurah::query()->firstOrFail();

        $repeatNames = [];
        for ($index = 1; $index <= 9; $index++) {
            $name = sprintf('Ulang %02d', $index);
            $repeatNames[] = $name;
            $santri = $this->makeSantri('20261'.str_pad((string) $index, 2, '0', STR_PAD_LEFT), $name);
            HafalanSetoran::query()->create([
                'santri_id' => $santri->id,
                'ustaz_user_id' => $fx['ustaz']->id,
                'activity_type' => 'ngaji',
                'category' => 'bacaan',
                'subtype' => 'alquran',
                'quran_surah_id' => 1,
                'setoran_date' => now()->toDateString(),
                'ayah_start' => 1,
                'ayah_end' => 3,
                'status' => SetoranStatus::Mengulang,
            ]);
        }

        $pendingNames = [];
        for ($index = 1; $index <= 9; $index++) {
            $name = sprintf('Belum %02d', $index);
            $pendingNames[] = $name;
            $this->makeSantri('20262'.str_pad((string) $index, 2, '0', STR_PAD_LEFT), $name);
        }

        $pengajar = $this->userWithRole(Role::Pengajar, ['username' => 'pengajar-daftar']);

        $this->actingAs($pengajar)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder(array_reverse($repeatNames))
            ->assertSeeInOrder($pendingNames)
            ->assertSee('Setoran perlu diulang · 10')
            ->assertSee('Belum setor hari ini · 10');
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
            'submitted_at' => now(),
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

    private function makeSantri(
        string $nis,
        string $name,
        Gender $gender = Gender::LakiLaki,
        SantriStatus $status = SantriStatus::Aktif,
    ): SantriProfile {
        $user = User::factory()->create([
            'name' => $name,
            'username' => $nis,
            'email' => $nis.'@santri.test',
        ]);
        $user->assignRole(Role::Santri);

        return SantriProfile::query()->create([
            'user_id' => $user->id,
            'nis' => $nis,
            'gender' => $gender,
            'status' => $status,
        ]);
    }
}
