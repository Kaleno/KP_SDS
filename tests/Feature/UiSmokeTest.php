<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SetoranStatus;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\SantriProfile;
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
        $this->travelTo('2026-09-23 10:00:00');
    }

    public function test_all_role_pages_render_and_progress_matches_across_surfaces(): void
    {
        $fx = $this->smokeFixture();

        $this->actingAs($fx['ketua'])->get(route('dashboard'))->assertOk()->assertSee('Santri aktif');
        $this->actingAs($fx['ketua'])->get(route('ketua.ustaz.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ketua.santri.index'))->assertOk()->assertSee('Ahmad Fauzi');
        $this->actingAs($fx['ketua'])->get(route('ketua.registrations.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ketua.holidays.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ketua.finance.index'))->assertOk()->assertSee('Pengaturan SPP');
        $this->actingAs($fx['ketua'])->get(route('ops.attendance.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ops.setoran.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ops.setoran.index', ['date_from' => 'bukan-tanggal']))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ops.setoran.index', ['date_from' => '2026-02-31']))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ops.spp.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('laporan.attendance.index', ['date_from' => 'bukan-tanggal']))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('ketua.setup.create'))->assertRedirect(route('ketua.santri.index'));
        $this->actingAs($fx['ketua'])->get(route('ops.setoran.create'))->assertOk()->assertSee('id="ayah_start"', false);
        $this->actingAs($fx['ketua'])->get(route('laporan.progress.index'))->assertRedirect(route('laporan.progress.bacaan.index'));
        $this->actingAs($fx['ketua'])->get(route('laporan.progress.bacaan.index'))->assertOk()->assertSee('4.73%')->assertSee('Bacaan')->assertSee('Hafalan');
        $this->actingAs($fx['ketua'])->get(route('laporan.progress.bacaan.show', $fx['santri'][0]))->assertOk()->assertSee('Al-Fatihah');
        $this->actingAs($fx['ketua'])->get(route('laporan.attendance.index'))->assertOk();
        $this->actingAs($fx['ketua'])->get(route('profile.edit'))->assertOk();

        $this->get('/ketua/halaqah')->assertNotFound();
        $this->get('/ketua/academic-years')->assertNotFound();

        $this->actingAs($fx['ustaz'])->get(route('dashboard'))->assertOk();
        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.index'))->assertOk();
        $session = AttendanceSession::query()->whereDate('session_date', now()->toDateString())->firstOrFail();
        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.show', $session))->assertOk()->assertSee('Ahmad Fauzi');

        $rows = [];
        foreach ($fx['santri'] as $index => $santri) {
            $rows[$santri->id] = [
                'status' => [AttendanceStatus::Izin, AttendanceStatus::Hadir, AttendanceStatus::Alfa][$index]->value,
            ];
        }
        $this->actingAs($fx['ustaz'])
            ->put(route('ops.attendance.update', $session), ['rows' => $rows])
            ->assertRedirect(route('ops.setoran.create', ['sesi' => $session->id]));

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.create', ['sesi' => $session->id]))
            ->assertOk()
            ->assertSee('Hasan Basri')
            ->assertSee('Cari nomor, nama surat, atau juz')
            ->assertDontSee('Yusuf Maulana')
            ->assertDontSee('Ahmad Fauzi');

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri'][1]->id,
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Mengulang->value,
            'sesi' => $session->id,
        ])->assertRedirect(route('ops.setoran.create', ['sesi' => $session->id]));

        $portal = $this->actingAs($fx['santri'][0]->user)->get(route('portal.home'));
        $portal->assertOk()->assertSee('4.73%')->assertSee('Al-Fatihah')->assertSee('Hadir')->assertDontSee('Simpan setoran');

        $this->actingAs($fx['admin'])->get(route('super-admin.ketua.index'))->assertOk();
        $this->actingAs($fx['admin'])->get(route('ketua.santri.index'))->assertForbidden();
        $this->actingAs($fx['admin'])->get(route('ops.attendance.index'))->assertForbidden();
    }

    /**
     * @return array{
     *     admin: User,
     *     ketua: User,
     *     ustaz: User,
     *     santri: list<SantriProfile>
     * }
     */
    private function smokeFixture(): array
    {
        $admin = $this->userWithRole(Role::SuperAdmin, ['username' => 'superadmin']);
        $ketua = $this->userWithRole(Role::Ketua, ['username' => 'ketua']);
        $ustaz = $this->userWithRole(Role::KetuaPengajar, ['username' => 'ustaz1']);

        $santri = [];
        foreach (['2026001' => 'Ahmad Fauzi', '2026002' => 'Hasan Basri', '2026003' => 'Yusuf Maulana'] as $nis => $name) {
            $santri[] = $this->makeSantri($nis, $name);
        }

        HafalanSetoran::query()->create([
            'santri_id' => $santri[0]->id,
            'ustaz_user_id' => $ustaz->id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lulus,
        ]);

        return compact('admin', 'ketua', 'ustaz', 'santri');
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
