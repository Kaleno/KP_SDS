<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SetoranStatus;
use App\Models\AcademicYear;
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

class UiSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(QuranSeeder::class);
    }

    public function test_all_role_pages_render_and_progress_matches_across_surfaces(): void
    {
        $fx = $this->smokeFixture();

        $this->actingAs($fx['ketua'])->get(route('dashboard'))->assertOk()->assertSee('Halaqah aktif');
        $this->actingAs($fx['ketua'])->get(route('ketua.academic-years.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ketua.locations.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ketua.ustaz.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ketua.santri.index'))->assertOk()->assertSee('Ahmad Fauzi');
        $this->actingAs($fx['ketua'])->get(route('ketua.orang-tua.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ketua.halaqah.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ketua.halaqah.show', $fx['halaqah']))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ops.attendance.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ops.setoran.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ops.setoran.index', ['date_from' => 'bukan-tanggal']))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ops.setoran.index', ['date_from' => '2026-02-31']))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('laporan.attendance.index', ['date_from' => 'bukan-tanggal']))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ops.setoran.create'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('laporan.progress.index'))->assertOk()->assertSee('4.73%');
        $this->actingAs($fx['ketua'])->get(route('laporan.progress.show', $fx['santri'][0]))->assertOk()->assertSee('7 / 6236');
        $this->actingAs($fx['ketua'])->get(route('laporan.attendance.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('profile.edit'))->assertOk();

        $this->actingAs($fx['ustaz'])->get(route('dashboard'))->assertOk();
        $this->actingAs($fx['ustaz'])->post(route('ops.attendance.open', $fx['schedule']))->assertRedirect();
        $session = $fx['schedule']->sessions()->first();
        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.show', $session))->assertOk()->assertSee('Ahmad Fauzi');

        $rows = [];
        foreach ($fx['santri'] as $index => $santri) {
            $rows[$santri->id] = [
                'status' => [AttendanceStatus::Hadir, AttendanceStatus::Izin, AttendanceStatus::Alfa][$index]->value,
            ];
        }
        $this->actingAs($fx['ustaz'])
            ->put(route('ops.attendance.update', $session), ['rows' => $rows])
            ->assertRedirect();

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri'][1]->id,
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Ulang->value,
        ])->assertRedirect(route('ops.setoran.index'));

        $portal = $this->actingAs($fx['santri'][0]->user)->get(route('portal.home'));
        $portal->assertOk()->assertSee('4.73%')->assertSee('Al-Fatihah')->assertSee('Hadir')->assertDontSee('Simpan setoran');

        $this->actingAs($fx['parent'])
            ->get(route('portal.home', ['anak' => $fx['santri'][0]->id]))
            ->assertOk()
            ->assertSee('4.73%');

        $this->actingAs($fx['parent'])
            ->get(route('portal.home', ['anak' => $fx['santri'][2]->id]))
            ->assertForbidden();

        $this->actingAs($fx['admin'])->get(route('super-admin.ketua.index'))->assertOk();
        $this->actingAs($fx['admin'])->get(route('ketua.santri.index'))->assertForbidden();
        $this->actingAs($fx['admin'])->get(route('ops.attendance.index'))->assertForbidden();
    }

    /**
     * @return array{
     *     admin: User,
     *     ketua: User,
     *     ustaz: User,
     *     parent: User,
     *     halaqah: Halaqah,
     *     schedule: Schedule,
     *     santri: list<SantriProfile>
     * }
     */
    private function smokeFixture(): array
    {
        $admin = $this->userWithRole(Role::SuperAdmin, ['username' => 'superadmin']);
        $ketua = $this->userWithRole(Role::Ketua, ['username' => 'ketua']);
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

        $parent = $this->userWithRole(Role::OrangTua, ['username' => 'ortu1']);
        $parent->children()->sync([$santri[0]->id, $santri[1]->id]);

        $schedule = Schedule::query()->create([
            'halaqah_id' => $halaqah->id,
            'location_id' => $location->id,
            'day_of_week' => now()->isoWeekday(),
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'is_active' => true,
        ]);

        HafalanSetoran::query()->create([
            'santri_id' => $santri[0]->id,
            'halaqah_id' => $halaqah->id,
            'ustaz_user_id' => $ustaz->id,
            'academic_year_id' => $year->id,
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lancar,
        ]);

        return compact('admin', 'ketua', 'ustaz', 'parent', 'halaqah', 'schedule', 'santri');
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
