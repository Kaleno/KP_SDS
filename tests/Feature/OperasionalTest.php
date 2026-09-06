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

class OperasionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_ustaz_can_open_today_session_and_save_mixed_status(): void
    {
        $fx = $this->opsFixture();

        $this->actingAs($fx['ustaz'])
            ->post(route('ops.attendance.open', $fx['schedule']))
            ->assertRedirect();

        $session = AttendanceSession::query()->first();
        $this->assertNotNull($session);
        $this->assertCount(3, $session->attendances);

        $rows = [];
        foreach ($fx['santri'] as $index => $santri) {
            $status = [AttendanceStatus::Hadir, AttendanceStatus::Izin, AttendanceStatus::Alfa][$index];
            $rows[$santri->id] = ['status' => $status->value];
        }

        $this->actingAs($fx['ustaz'])
            ->put(route('ops.attendance.update', $session), ['rows' => $rows])
            ->assertRedirect();

        $this->assertSame(1, Attendance::query()->where('status', AttendanceStatus::Alfa)->count());
        $this->assertSame(1, Attendance::query()->where('status', AttendanceStatus::Izin)->count());
    }

    public function test_other_ustaz_cannot_see_or_open_session(): void
    {
        $fx = $this->opsFixture();
        $outsider = $this->userWithRole(Role::Ustaz, ['username' => 'ustaz2']);

        $this->actingAs($outsider)
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertDontSee($fx['halaqah']->name);

        $this->actingAs($outsider)
            ->post(route('ops.attendance.open', $fx['schedule']))
            ->assertForbidden();

        $session = AttendanceSession::query()->create([
            'schedule_id' => $fx['schedule']->id,
            'session_date' => now()->toDateString(),
            'opened_by_user_id' => $fx['ustaz']->id,
        ]);

        $this->actingAs($outsider)
            ->get(route('ops.attendance.show', $session))
            ->assertForbidden();
    }

    public function test_ustaz_can_store_setoran_and_correct_it(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri'][0]->id,
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lancar->value,
            'note' => 'Al-Fatihah',
        ])->assertRedirect(route('ops.setoran.index'));

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri'][1]->id,
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Ulang->value,
        ])->assertRedirect();

        $setoran = HafalanSetoran::query()->where('santri_id', $fx['santri'][0]->id)->first();
        $this->assertSame(SetoranStatus::Lancar, $setoran->status);

        $this->actingAs($fx['ustaz'])->put(route('ops.setoran.update', $setoran), [
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 5,
            'status' => SetoranStatus::Perbaikan->value,
            'correction_note' => 'Salah rentang ayat',
        ])->assertRedirect(route('ops.setoran.index'));

        $this->assertSame(SetoranStatus::Perbaikan, $setoran->fresh()->status);
        $this->assertSame('Salah rentang ayat', $setoran->fresh()->correction_note);
    }

    public function test_setoran_rejects_ayah_beyond_surah(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri'][0]->id,
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 8,
            'status' => SetoranStatus::Lancar->value,
        ])->assertSessionHasErrors('ayah_end');
    }

    public function test_ketua_can_open_any_halaqah_session(): void
    {
        $fx = $this->opsFixture();

        $this->actingAs($fx['ketua'])
            ->post(route('ops.attendance.open', $fx['schedule']))
            ->assertRedirect();

        $this->assertTrue(AttendanceSession::query()->exists());
    }

    public function test_super_admin_cannot_open_ops_menu(): void
    {
        $admin = $this->userWithRole(Role::SuperAdmin);

        $this->actingAs($admin)
            ->get(route('ops.attendance.index'))
            ->assertForbidden();
    }

    /**
     * @return array{ketua: User, ustaz: User, halaqah: Halaqah, schedule: Schedule, santri: list<SantriProfile>}
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

        return compact('ketua', 'ustaz', 'halaqah', 'schedule', 'santri');
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
