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
use App\Models\SantriProfile;
use App\Models\Schedule;
use App\Models\User;
use App\Support\Role;
use Database\Seeders\QuranSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_santri_sees_same_progress_and_setoran_without_input_form(): void
    {
        $fx = $this->portalFixture();
        $this->seed(QuranSeeder::class);
        $this->storeFatihah($fx, $fx['santri'][0], SetoranStatus::Lancar);

        $this->actingAs($fx['santri'][0]->user)
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Al-Fatihah')
            ->assertSee('4.73%')
            ->assertSee('hanya melihat')
            ->assertDontSee('Input setoran')
            ->assertDontSee('Simpan setoran');
    }

    public function test_parent_can_switch_linked_children_but_not_unlinked(): void
    {
        $fx = $this->portalFixture();
        $this->seed(QuranSeeder::class);
        $this->storeFatihah($fx, $fx['santri'][0], SetoranStatus::Lancar);
        $this->storeFatihah($fx, $fx['santri'][1], SetoranStatus::Ulang);

        $parent = $this->userWithRole(Role::OrangTua, ['username' => 'ortu1', 'name' => 'Bapak Abdullah']);
        $parent->children()->sync([$fx['santri'][0]->id, $fx['santri'][1]->id]);

        $this->actingAs($parent)
            ->get(route('portal.home', ['anak' => $fx['santri'][0]->id]))
            ->assertOk()
            ->assertSee('Ahmad Fauzi')
            ->assertSee('4.73%')
            ->assertSee('Hasan Basri');

        $this->actingAs($parent)
            ->get(route('portal.home', ['anak' => $fx['santri'][1]->id]))
            ->assertOk()
            ->assertSee('Hasan Basri')
            ->assertSee('Ulang');

        $this->actingAs($parent)
            ->get(route('portal.home', ['anak' => $fx['santri'][2]->id]))
            ->assertForbidden();
    }

    public function test_other_parent_cannot_see_unlinked_child(): void
    {
        $fx = $this->portalFixture();
        $outsider = $this->userWithRole(Role::OrangTua, ['username' => 'ortu2']);

        $this->actingAs($outsider)
            ->get(route('portal.home', ['anak' => $fx['santri'][0]->id]))
            ->assertForbidden();
    }

    public function test_santri_cannot_open_ops_or_other_child_query(): void
    {
        $fx = $this->portalFixture();

        $this->actingAs($fx['santri'][0]->user)
            ->get(route('ops.setoran.create'))
            ->assertForbidden();

        $this->actingAs($fx['santri'][0]->user)
            ->post(route('ops.setoran.store'), [])
            ->assertForbidden();

        $this->actingAs($fx['santri'][0]->user)
            ->get(route('portal.home', ['anak' => $fx['santri'][1]->id]))
            ->assertForbidden();
    }

    public function test_portal_shows_attendance_and_schedule(): void
    {
        $fx = $this->portalFixture();
        $session = AttendanceSession::query()->create([
            'schedule_id' => $fx['schedule']->id,
            'session_date' => now()->toDateString(),
            'opened_by_user_id' => $fx['ustaz']->id,
        ]);
        Attendance::query()->create([
            'attendance_session_id' => $session->id,
            'santri_id' => $fx['santri'][0]->id,
            'status' => AttendanceStatus::Hadir,
        ]);

        $this->actingAs($fx['santri'][0]->user)
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Hadir')
            ->assertSee('Jadwal halaqah');
    }

    /**
     * @return array{ustaz: User, year: AcademicYear, halaqah: Halaqah, schedule: Schedule, santri: list<SantriProfile>}
     */
    private function portalFixture(): array
    {
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

        return compact('ustaz', 'year', 'halaqah', 'schedule', 'santri');
    }

    /**
     * @param  array{ustaz: User, year: AcademicYear, halaqah: Halaqah}  $fx
     */
    private function storeFatihah(array $fx, SantriProfile $santri, SetoranStatus $status): void
    {
        HafalanSetoran::query()->create([
            'santri_id' => $santri->id,
            'halaqah_id' => $fx['halaqah']->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'academic_year_id' => $fx['year']->id,
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => $status,
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
