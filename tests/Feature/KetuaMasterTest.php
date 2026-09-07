<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\SantriStatus;
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

    public function test_parent_can_link_two_children_by_nis(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $first = $this->makeSantri('2026001', 'Anak Satu');
        $second = $this->makeSantri('2026002', 'Anak Dua');
        $parent = $this->userWithRole(Role::OrangTua, ['username' => 'ortu1']);

        $this->actingAs($ketua)
            ->post(route('ketua.orang-tua.attach-child', $parent), ['nis' => '2026001'])
            ->assertRedirect();
        $this->actingAs($ketua)
            ->post(route('ketua.orang-tua.attach-child', $parent), ['nis' => '2026002'])
            ->assertRedirect();

        $this->assertCount(2, $parent->fresh()->children);
        $this->assertTrue($parent->children->contains($first));
        $this->assertTrue($parent->children->contains($second));
    }

    public function test_santri_cannot_join_two_active_halaqah_in_same_year(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $year = $this->makeYear();
        $ustaz = $this->userWithRole(Role::Ustaz);
        $santri = $this->makeSantri('2026001', 'Ahmad');
        $first = $this->makeHalaqah($year, $ustaz, 'Halaqah A');
        $second = $this->makeHalaqah($year, $ustaz, 'Halaqah B');

        $this->actingAs($ketua)->post(route('ketua.halaqah.members.store', $first), [
            'santri_id' => $santri->id,
            'started_at' => '2026-07-01',
        ])->assertRedirect();

        $this->actingAs($ketua)->post(route('ketua.halaqah.members.store', $second), [
            'santri_id' => $santri->id,
            'started_at' => '2026-07-02',
        ])->assertSessionHasErrors('santri_id');

        $this->assertSame(1, HalaqahMember::query()->whereNull('ended_at')->count());
    }

    public function test_mutation_moves_santri_to_another_halaqah(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $year = $this->makeYear();
        $ustaz = $this->userWithRole(Role::Ustaz);
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
        ])->assertRedirect(route('ketua.halaqah.show', $second));

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
        $ustaz = $this->userWithRole(Role::Ustaz);

        $this->actingAs($ustaz)
            ->get(route('ketua.setup.create'))
            ->assertForbidden();

        $this->actingAs($ustaz)
            ->post(route('ketua.setup.store'), $this->wizardPayload())
            ->assertForbidden();
    }

    public function test_empty_ketua_wizard_creates_year_location_ustaz_santri_halaqah_and_schedule(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('ketua.setup.create'))
            ->assertOk()
            ->assertSee('Siapkan halaqah')
            ->assertSee('Tahun ajaran')
            ->assertSee('Lokasi pertemuan');

        $this->actingAs($ketua)
            ->post(route('ketua.setup.store'), $this->wizardPayload())
            ->assertRedirect();

        $year = AcademicYear::query()->where('name', '2026/2027')->first();
        $location = Location::query()->where('name', 'Masjid Utara')->first();
        $ustaz = User::query()->where('username', 'ustazbaru')->first();
        $santri = SantriProfile::query()->where('nis', '2026999')->first();
        $halaqah = Halaqah::query()->where('name', 'Halaqah Tahfidz A')->first();

        $this->assertNotNull($year);
        $this->assertTrue($year->is_active);
        $this->assertNotNull($location);
        $this->assertNotNull($ustaz);
        $this->assertTrue($ustaz->hasRole(Role::Ustaz));
        $this->assertNotNull($santri);
        $this->assertSame('Ahmad Fauzi', $santri->user->name);
        $this->assertNotNull($halaqah);
        $this->assertSame($year->id, $halaqah->academic_year_id);
        $this->assertSame($ustaz->id, $halaqah->ustaz_user_id);
        $this->assertTrue(
            HalaqahMember::query()
                ->where('halaqah_id', $halaqah->id)
                ->where('santri_id', $santri->id)
                ->whereNull('ended_at')
                ->exists()
        );
        $this->assertTrue(
            Schedule::query()
                ->where('halaqah_id', $halaqah->id)
                ->where('location_id', $location->id)
                ->where('day_of_week', 1)
                ->where('start_time', '07:00:00')
                ->where('end_time', '08:30:00')
                ->exists()
        );

        $this->actingAs($ketua)
            ->get(route('ketua.halaqah.show', $halaqah))
            ->assertOk()
            ->assertSee('Halaqah Tahfidz A')
            ->assertSee('Ahmad Fauzi');

        $this->actingAs($ketua)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Halaqah belum siap dipakai');
    }

    public function test_ketua_wizard_can_reuse_existing_master_data(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $year = $this->makeYear();
        $location = Location::query()->create(['name' => 'Masjid Utama']);
        $ustaz = $this->userWithRole(Role::Ustaz, ['username' => 'ustaz1', 'name' => 'Ustaz Ahmad']);
        $santri = $this->makeSantri('2026001', 'Ahmad Fauzi');

        $this->actingAs($ketua)->post(route('ketua.setup.store'), [
            'year_source' => 'existing',
            'academic_year_id' => $year->id,
            'location_source' => 'existing',
            'location_id' => $location->id,
            'ustaz_source' => 'existing',
            'ustaz_user_id' => $ustaz->id,
            'santri_source' => 'existing',
            'santri_id' => $santri->id,
            'halaqah_name' => 'Halaqah Tahfidz B',
            'day_of_week' => 2,
            'start_time' => '09:00',
            'end_time' => '10:30',
        ])->assertRedirect();

        $halaqah = Halaqah::query()->where('name', 'Halaqah Tahfidz B')->first();
        $this->assertNotNull($halaqah);
        $this->assertSame($year->id, $halaqah->academic_year_id);
        $this->assertSame($ustaz->id, $halaqah->ustaz_user_id);
        $this->assertSame(1, User::query()->role(Role::Ustaz)->count());
        $this->assertSame(1, SantriProfile::query()->count());
        $this->assertTrue(
            Schedule::query()
                ->where('halaqah_id', $halaqah->id)
                ->where('location_id', $location->id)
                ->where('day_of_week', 2)
                ->exists()
        );
    }

    public function test_ketua_wizard_rejects_empty_payload(): void
    {
        $ketua = $this->userWithRole(Role::Ketua);

        $this->actingAs($ketua)
            ->post(route('ketua.setup.store'), [])
            ->assertSessionHasErrors(['year_source', 'location_source', 'ustaz_source', 'santri_source', 'halaqah_name']);
    }

    /**
     * @return array<string, mixed>
     */
    private function wizardPayload(): array
    {
        return [
            'year_source' => 'new',
            'year_name' => '2026/2027',
            'year_start_date' => '2026-07-01',
            'year_end_date' => '2027-06-30',
            'location_source' => 'new',
            'location_name' => 'Masjid Utara',
            'ustaz_source' => 'new',
            'ustaz_name' => 'Ustaz Baru',
            'ustaz_username' => 'ustazbaru',
            'ustaz_password' => 'password',
            'ustaz_password_confirmation' => 'password',
            'santri_source' => 'new',
            'santri_name' => 'Ahmad Fauzi',
            'santri_nis' => '2026999',
            'santri_gender' => Gender::LakiLaki->value,
            'santri_password' => 'password',
            'santri_password_confirmation' => 'password',
            'halaqah_name' => 'Halaqah Tahfidz A',
            'day_of_week' => 1,
            'start_time' => '07:00',
            'end_time' => '08:30',
        ];
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
