<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Models\AcademicYear;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KetuaMasterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_cannot_open_ketua_master(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::SuperAdmin);

        $this->actingAs($admin)
            ->get(route('ketua.santri.index'))
            ->assertForbidden();
    }

    public function test_ketua_can_create_academic_year_and_keep_single_active(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)->post(route('ketua.academic-years.store'), [
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => '1',
        ])->assertRedirect();

        $this->actingAs($ketua)->post(route('ketua.academic-years.store'), [
            'name' => '2027/2028',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertSame(1, AcademicYear::query()->where('is_active', true)->count());
        $this->assertTrue(AcademicYear::query()->where('name', '2027/2028')->first()->is_active);
    }

    public function test_ketua_can_create_ketua_pengajar_and_pengajar(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)->post(route('ketua.ustaz.store'), [
            'teaching_role' => Role::KetuaPengajar,
            'name' => 'Ketua Pengajar Satu',
            'username' => 'kp1',
            'email' => null,
            'phone' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('ketua.ustaz.index'));

        $this->actingAs($ketua)->post(route('ketua.ustaz.store'), [
            'teaching_role' => Role::Pengajar,
            'name' => 'Pengajar Dua',
            'username' => 'pjr1',
            'email' => null,
            'phone' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('ketua.ustaz.index'));

        $this->assertTrue(User::query()->where('username', 'kp1')->first()->hasRole(Role::KetuaPengajar));
        $this->assertTrue(User::query()->where('username', 'pjr1')->first()->hasRole(Role::Pengajar));
    }

    public function test_santri_can_join_two_active_kelas(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $year = $this->makeYear();
        $ustaz = $this->userWithRole(Role::KetuaPengajar);
        $santri = $this->makeSantri('2026001', 'Ahmad');
        $first = $this->makeHalaqah($year, $ustaz, 'Kelas A');
        $second = $this->makeHalaqah($year, $ustaz, 'Kelas B');

        $this->actingAs($ketua)->post(route('ketua.halaqah.members.store', $first), [
            'santri_id' => $santri->id,
            'started_at' => '2026-07-01',
        ])->assertRedirect();

        $this->actingAs($ketua)->post(route('ketua.halaqah.members.store', $second), [
            'santri_id' => $santri->id,
            'started_at' => '2026-07-02',
        ])->assertRedirect();

        $this->assertSame(2, HalaqahMember::query()->whereNull('ended_at')->count());
    }

    public function test_mutation_moves_santri_to_another_halaqah(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $year = $this->makeYear();
        $ustaz = $this->userWithRole(Role::KetuaPengajar);
        $santri = $this->makeSantri('2026001', 'Ahmad');
        $first = $this->makeHalaqah($year, $ustaz, 'Halaqah A');
        $second = $this->makeHalaqah($year, $ustaz, 'Halaqah B');

        $this->actingAs($ketua)->post(route('ketua.halaqah.members.store', $first), [
            'santri_id' => $santri->id,
            'started_at' => '2026-07-01',
        ]);

        $member = HalaqahMember::query()->where('halaqah_id', $first->id)->first();

        $this->actingAs($ketua)->post(route('ketua.halaqah.members.mutate', [$first, $member]), [
            'target_halaqah_id' => $second->id,
            'moved_at' => '2026-08-01',
            'mutation_note' => 'Pindah kelompok',
        ])->assertRedirect(route('ketua.halaqah.edit', $second));

        $this->assertNotNull($member->fresh()->ended_at);
        $this->assertTrue(
            HalaqahMember::query()
                ->where('halaqah_id', $second->id)
                ->where('santri_id', $santri->id)
                ->whereNull('ended_at')
                ->exists()
        );
    }

    public function test_guest_is_redirected_from_setup_wizard(): void
    {
        $this->get(route('ketua.setup.create'))
            ->assertRedirect(route('login'));
    }

    public function test_ustaz_cannot_open_setup_wizard(): void
    {
        $ustaz = $this->userWithRole(Role::KetuaPengajar);

        $this->actingAs($ustaz)
            ->get(route('ketua.setup.create'))
            ->assertForbidden();
    }

    private function userWithRole(string $role, array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        $user->assignRole($role);

        return $user;
    }

    private function makeYear(): AcademicYear
    {
        $year = AcademicYear::query()->create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);
        $year->markAsActive();

        return $year;
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

    private function makeHalaqah(AcademicYear $year, User $ustaz, string $name): Halaqah
    {
        return Halaqah::query()->create([
            'academic_year_id' => $year->id,
            'ustaz_user_id' => $ustaz->id,
            'name' => $name,
            'is_active' => true,
        ]);
    }
}
