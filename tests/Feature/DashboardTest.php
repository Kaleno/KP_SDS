<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SetoranStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\Location;
use App\Models\QuranSurah;
use App\Models\SantriProfile;
use App\Models\Schedule;
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
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_ketua_dashboard_shows_operational_today_summary(): void
    {
        $fx = $this->opsFixture();
        $this->seedTodayActivity($fx);

        $this->actingAs($fx['ketua'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Halaqah aktif')
            ->assertSee('Santri aktif')
            ->assertSee('Setoran hari ini')
            ->assertSee('Alfa hari ini')
            ->assertSee('2/3 anggota sudah setor')
            ->assertSee('Kehadiran hari ini')
            ->assertSee('Halaqah Tahfidz A')
            ->assertSee('07:00')
            ->assertSee('Masjid Utama')
            ->assertSee('Sesi terbuka')
            ->assertSee('Perlu perhatian')
            ->assertSee('Yusuf Maulana')
            ->assertSee('Hasan Basri')
            ->assertSee('Al-Fatihah')
            ->assertSee('Belum setor hari ini')
            ->assertSee('Setoran perlu diulang');
    }

    public function test_ustaz_dashboard_shows_their_halaqah_activity(): void
    {
        $fx = $this->opsFixture();
        $this->seedTodayActivity($fx);

        $this->actingAs($fx['ustaz'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Anggota aktif')
            ->assertSee('Sesi hari ini')
            ->assertSee('Setoran hari ini')
            ->assertSee('Alfa hari ini')
            ->assertSee('Halaqah Anda')
            ->assertSee('Halaqah Tahfidz A')
            ->assertSee('07:00')
            ->assertSee('Yusuf Maulana')
            ->assertSee('Al-Fatihah')
            ->assertSee('Hasan Basri');
    }

    public function test_other_ustaz_does_not_see_foreign_halaqah_on_dashboard(): void
    {
        $fx = $this->opsFixture();
        $this->seedTodayActivity($fx);
        $outsider = $this->userWithRole(Role::Ustaz, ['username' => 'ustaz2']);

        $this->actingAs($outsider)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Halaqah Tahfidz A')
            ->assertDontSee('Yusuf Maulana')
            ->assertDontSee('Al-Fatihah');
    }

    /**
     * @return array{ketua: User, ustaz: User, year: AcademicYear, halaqah: Halaqah, schedule: Schedule, santri: list<SantriProfile>}
     */
    private function opsFixture(): array
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole(Role::Ustaz, ['username' => 'ustaz1', 'name' => 'Ustaz Ahmad']);
        $year = AcademicYear::query()->create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $location = Location::query()->create(['name' => 'Masjid Utama']);
        $halaqah = Halaqah::query()->create([
            'academic_year_id' => $year->id,
            'ustaz_user_id' => $ustaz->id,
            'name' => 'Halaqah Tahfidz A',
            'is_active' => true,
        ]);

        $santri = [];
        foreach (['2026001' => 'Ahmad Fauzi', '2026002' => 'Hasan Basri', '2026003' => 'Yusuf Maulana'] as $nis => $name) {
            $profile = $this->makeSantri($nis, $name);
            HalaqahMember::query()->create([
                'halaqah_id' => $halaqah->id,
                'santri_id' => $profile->id,
                'academic_year_id' => $year->id,
                'started_at' => '2026-07-01',
            ]);
            $santri[] = $profile;
        }

        $schedule = Schedule::query()->create([
            'halaqah_id' => $halaqah->id,
            'location_id' => $location->id,
            'day_of_week' => now()->isoWeekday(),
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'is_active' => true,
        ]);

        return compact('ketua', 'ustaz', 'year', 'halaqah', 'schedule', 'santri');
    }

    /**
     * @param  array{ustaz: User, year: AcademicYear, halaqah: Halaqah, schedule: Schedule, santri: list<SantriProfile>}  $fx
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
            'schedule_id' => $fx['schedule']->id,
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
            'halaqah_id' => $fx['halaqah']->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'academic_year_id' => $fx['year']->id,
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lancar,
        ]);
        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri'][1]->id,
            'halaqah_id' => $fx['halaqah']->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'academic_year_id' => $fx['year']->id,
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Ulang,
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
