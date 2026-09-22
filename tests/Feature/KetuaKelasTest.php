<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\Location;
use App\Models\SantriProfile;
use App\Models\Schedule;
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

    public function test_guest_is_redirected_from_kelas(): void
    {
        $this->get(route('ketua.halaqah.create'))
            ->assertRedirect(route('login'));
    }

    public function test_ustaz_cannot_open_kelas(): void
    {
        $ustaz = $this->userWithRole(Role::KetuaPengajar);

        $this->actingAs($ustaz)
            ->get(route('ketua.halaqah.create'))
            ->assertForbidden();
    }

    public function test_setup_wizard_redirects_to_create_kelas(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('ketua.setup.create'))
            ->assertRedirect(route('ketua.halaqah.create'));
    }

    public function test_create_kelas_form_renders_day_shortcuts(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $this->userWithRole(Role::KetuaPengajar, ['username' => 'ustaz1']);

        $this->actingAs($ketua)
            ->get(route('ketua.halaqah.create'))
            ->assertOk()
            ->assertSee('Buat kelas')
            ->assertSee('Setiap hari')
            ->assertSee('Senin–Jumat')
            ->assertSee('Semua santri aktif ikut otomatis');
    }

    public function test_empty_payload_is_rejected(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)
            ->post(route('ketua.halaqah.store'), [])
            ->assertSessionHasErrors(['name', 'ustaz_user_id', 'days', 'start_time', 'end_time']);
    }

    public function test_creating_kelas_makes_year_location_schedules_and_enrolls_active_santri(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole(Role::KetuaPengajar, ['username' => 'ustaz1', 'name' => 'Ustaz Ahmad']);
        $first = $this->makeSantri('2026001', 'Ahmad Fauzi');
        $second = $this->makeSantri('2026002', 'Hasan Basri');
        $this->makeSantri('2026003', 'Yusuf Lulus', aktif: false);

        $this->actingAs($ketua)
            ->post(route('ketua.halaqah.store'), $this->kelasPayload($ustaz, 'Tahfidz pagi', [1, 2, 3, 4, 5]))
            ->assertRedirect(route('ketua.halaqah.index'));

        $kelas = Halaqah::query()->where('name', 'Tahfidz pagi')->first();
        $this->assertNotNull($kelas);
        $this->assertSame($ustaz->id, $kelas->ustaz_user_id);
        $this->assertTrue(AcademicYear::query()->where('is_active', true)->exists());
        $this->assertTrue(Location::query()->where('name', 'Tempat utama')->exists());
        $this->assertSame(5, Schedule::query()->where('halaqah_id', $kelas->id)->where('is_active', true)->count());
        $this->assertTrue(
            HalaqahMember::query()->where('halaqah_id', $kelas->id)->where('santri_id', $first->id)->whereNull('ended_at')->exists()
        );
        $this->assertTrue(
            HalaqahMember::query()->where('halaqah_id', $kelas->id)->where('santri_id', $second->id)->whereNull('ended_at')->exists()
        );
        $this->assertFalse(
            HalaqahMember::query()->where('halaqah_id', $kelas->id)->where('santri_id', SantriProfile::query()->where('nis', '2026003')->value('id'))->whereNull('ended_at')->exists()
        );

        $this->actingAs($ketua)
            ->get(route('ketua.halaqah.index'))
            ->assertOk()
            ->assertSee('Tahfidz pagi')
            ->assertSee('Senin–Jumat');
    }

    public function test_second_kelas_also_enrolls_the_same_active_santri(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole(Role::KetuaPengajar, ['username' => 'ustaz1']);
        $santri = $this->makeSantri('2026001', 'Ahmad Fauzi');

        $this->actingAs($ketua)->post(route('ketua.halaqah.store'), $this->kelasPayload($ustaz, 'Kelas pagi', [1]));
        $this->actingAs($ketua)->post(route('ketua.halaqah.store'), $this->kelasPayload($ustaz, 'Kelas sore', [2], '15:00', '16:30'));

        $this->assertSame(2, HalaqahMember::query()->where('santri_id', $santri->id)->whereNull('ended_at')->count());
    }

    public function test_updating_kelas_syncs_days_and_keeps_sessions_as_inactive(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole(Role::KetuaPengajar, ['username' => 'ustaz1']);

        $this->actingAs($ketua)->post(route('ketua.halaqah.store'), $this->kelasPayload($ustaz, 'Tahfidz', [1, 2]));

        $kelas = Halaqah::query()->where('name', 'Tahfidz')->first();
        $monday = Schedule::query()->where('halaqah_id', $kelas->id)->where('day_of_week', 1)->first();
        $monday->sessions()->create([
            'session_date' => now()->toDateString(),
            'opened_by_user_id' => $ketua->id,
        ]);

        $this->actingAs($ketua)
            ->put(route('ketua.halaqah.update', $kelas), $this->kelasPayload($ustaz, 'Tahfidz', [2, 3]))
            ->assertRedirect(route('ketua.halaqah.index'));

        $this->assertFalse($monday->fresh()->is_active);
        $this->assertTrue(Schedule::query()->where('halaqah_id', $kelas->id)->where('day_of_week', 2)->where('is_active', true)->exists());
        $this->assertTrue(Schedule::query()->where('halaqah_id', $kelas->id)->where('day_of_week', 3)->where('is_active', true)->exists());
    }

    public function test_new_active_santri_joins_existing_kelas(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole(Role::KetuaPengajar, ['username' => 'ustaz1']);

        $this->actingAs($ketua)->post(route('ketua.halaqah.store'), $this->kelasPayload($ustaz, 'Tahfidz', [1]));

        $this->actingAs($ketua)->post(route('ketua.santri.store'), [
            'name' => 'Santri Baru',
            'nis' => '2026999',
            'gender' => 'laki-laki',
            'track' => 'alquran',
            'status' => 'aktif',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('ketua.santri.index'));

        $santri = SantriProfile::query()->where('nis', '2026999')->first();
        $kelas = Halaqah::query()->where('name', 'Tahfidz')->first();

        $this->assertTrue(
            HalaqahMember::query()->where('halaqah_id', $kelas->id)->where('santri_id', $santri->id)->whereNull('ended_at')->exists()
        );
    }

    /**
     * @param  list<int>  $days
     * @return array<string, mixed>
     */
    private function kelasPayload(User $ustaz, string $name, array $days, string $start = '07:00', string $end = '08:30'): array
    {
        return [
            'name' => $name,
            'ustaz_user_id' => $ustaz->id,
            'days' => $days,
            'start_time' => $start,
            'end_time' => $end,
            'is_active' => '1',
        ];
    }

    private function userWithRole(string $role, array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        $user->assignRole($role);

        return $user;
    }

    private function makeSantri(string $nis, string $name, bool $aktif = true): SantriProfile
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
            'gender' => 'laki-laki',
            'status' => $aktif ? 'aktif' : 'lulus',
        ]);
    }
}
