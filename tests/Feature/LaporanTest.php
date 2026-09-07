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
use Database\Seeders\QuranSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_ketua_dashboard_shows_today_counts(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();
        $this->openMixedAttendance($fx);
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

        $this->actingAs($fx['ketua'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Halaqah aktif')
            ->assertSee('Santri aktif')
            ->assertSee('Setoran hari ini')
            ->assertSee('Alfa hari ini');
    }

    public function test_setoran_history_can_filter_by_status(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();

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

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index', ['status' => SetoranStatus::Ulang->value]))
            ->assertOk()
            ->assertSee('Hasan Basri');
    }

    public function test_attendance_recap_counts_alfa_per_santri(): void
    {
        $fx = $this->opsFixture();
        $this->openMixedAttendance($fx);

        $this->actingAs($fx['ketua'])
            ->get(route('laporan.attendance.index', [
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Yusuf Maulana')
            ->assertSee('Rekap kelompok');
    }

    public function test_other_ustaz_cannot_open_progress_of_foreign_santri(): void
    {
        $fx = $this->opsFixture();
        $this->seed(QuranSeeder::class);
        $outsider = $this->userWithRole(Role::Ustaz, ['username' => 'ustaz2']);

        $this->actingAs($outsider)
            ->get(route('laporan.progress.show', $fx['santri'][0]))
            ->assertForbidden();

        $this->actingAs($fx['ustaz'])
            ->get(route('laporan.progress.show', $fx['santri'][0]))
            ->assertOk()
            ->assertSee('Ahmad Fauzi');
    }

    public function test_ops_and_recap_hide_other_academic_years(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();

        $oldYear = AcademicYear::query()->create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => false,
        ]);
        $oldHalaqah = Halaqah::query()->create([
            'academic_year_id' => $oldYear->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'name' => 'Halaqah Lama',
            'is_active' => true,
        ]);
        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri'][0]->id,
            'halaqah_id' => $oldHalaqah->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'academic_year_id' => $oldYear->id,
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lancar,
        ]);

        $this->actingAs($fx['ketua'])
            ->get(route('ops.setoran.index'))
            ->assertOk()
            ->assertDontSee('Halaqah Lama');

        $this->actingAs($fx['ketua'])
            ->get(route('laporan.attendance.index'))
            ->assertOk()
            ->assertDontSee('Halaqah Lama');
    }

    public function test_progress_page_shows_juz_one_after_fatihah_lancar(): void
    {
        $fx = $this->opsFixture();
        $this->seed(QuranSeeder::class);

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

        $this->actingAs($fx['ketua'])
            ->get(route('laporan.progress.show', $fx['santri'][0]))
            ->assertOk()
            ->assertSee('4.73%')
            ->assertSee('7 / 6236');

        $this->actingAs($fx['ketua'])
            ->get(route('laporan.progress.index'))
            ->assertOk()
            ->assertSee('4.73%')
            ->assertSee('Juz 1 dikerjakan');
    }

    /**
     * @return array{ketua: User, ustaz: User, year: AcademicYear, halaqah: Halaqah, schedule: Schedule, santri: list<SantriProfile>}
     */
    private function opsFixture(): array
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole(Role::Ustaz, ['username' => 'ustaz1']);
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
     * @param  array{ustaz: User, schedule: Schedule, santri: list<SantriProfile>}  $fx
     */
    private function openMixedAttendance(array $fx): AttendanceSession
    {
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

        return $session;
    }

    private function seedFatihah(): void
    {
        QuranSurah::query()->create([
            'id' => 1,
            'name_id' => 'Al-Fatihah',
            'name_ar' => 'الفاتحة',
            'ayah_count' => 7,
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
