<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Models\AttendanceSession;
use App\Models\Holiday;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\Role;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoAttendanceSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_attendance_index_auto_creates_session_on_weekday(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00')); // Wednesday
        $fx = $this->fixture();

        $this->assertSame(0, AttendanceSession::query()->count());

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Isi absensi')
            ->assertSee('Semua santri aktif')
            ->assertDontSee('Buka sesi');

        $this->assertSame(1, AttendanceSession::query()->count());
        $session = AttendanceSession::query()->first();
        $this->assertSame('2026-09-23', $session->session_date->toDateString());
        $this->assertSame($fx['ustaz']->id, $session->opened_by_user_id);
    }

    public function test_attendance_index_skips_session_on_holiday(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $fx = $this->fixture();

        Holiday::query()->create([
            'date' => '2026-09-23',
            'name' => 'Libur nasional',
        ]);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Libur: Libur nasional')
            ->assertDontSee('Isi absensi')
            ->assertDontSee('Buka sesi');

        $this->assertSame(0, AttendanceSession::query()->count());
    }

    public function test_attendance_index_skips_session_on_saturday(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-26 10:00:00')); // Saturday
        $fx = $this->fixture();

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Hari libur (Sabtu/Minggu)')
            ->assertDontSee('Isi absensi')
            ->assertDontSee('Buka sesi');

        $this->assertSame(0, AttendanceSession::query()->count());
    }

    public function test_dashboard_auto_creates_session_and_shows_isi_absensi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $fx = $this->fixture();

        $this->actingAs($fx['ketua'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Isi absensi')
            ->assertSee('Sesi terbuka')
            ->assertDontSee('Buka sesi')
            ->assertDontSee('Belum dibuka');

        $this->assertSame(1, AttendanceSession::query()->count());
    }

    public function test_attendance_surfaces_use_date_without_kelas_framing(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $fx = $this->fixture();

        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.index'))->assertOk();
        $session = AttendanceSession::query()->firstOrFail();

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Hari ini')
            ->assertSee('Rabu, 23 September 2026')
            ->assertSee('Semua santri aktif')
            ->assertDontSee('Halaqah')
            ->assertDontSee('07:00')
            ->assertDontSee('Masjid Utama');

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.show', $session))
            ->assertOk()
            ->assertSee('Rabu, 23 September 2026')
            ->assertDontSee('07:00')
            ->assertDontSee('Masjid Utama');
    }

    public function test_pengajar_without_kelas_assignment_sees_all_active_santri(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $this->makeSantri('2026801', 'Ahmad');

        $pengajar = $this->userWithRole(Role::Pengajar, ['username' => 'lonely']);

        $this->actingAs($pengajar)
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Isi absensi')
            ->assertSee('Semua santri aktif')
            ->assertDontSee('Belum ada kelas yang ditugaskan kepada Anda')
            ->assertDontSee('Tidak ada jadwal untuk');
    }

    public function test_pengajar_sees_same_attendance_ui_as_ketua_pengajar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $fx = $this->fixture(Role::Pengajar);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Hari ini')
            ->assertSee('Rabu, 23 September 2026')
            ->assertSee('Isi absensi')
            ->assertDontSee('Buka sesi');

        $this->actingAs($fx['ustaz'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Absensi hari ini')
            ->assertSee('Rabu, 23 September 2026')
            ->assertSee('Isi absensi');
    }

    public function test_attendance_history_excludes_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $fx = $this->fixture();

        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.index'))->assertOk();

        AttendanceSession::query()->create([
            'session_date' => '2026-09-22',
            'opened_by_user_id' => $fx['ustaz']->id,
        ]);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Selasa, 22 September 2026')
            ->assertSee('Riwayat sesi');
    }

    public function test_attendance_index_prompts_when_no_active_santri(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $ustaz = $this->userWithRole(Role::Pengajar, ['username' => 'ustaz1']);

        $this->actingAs($ustaz)
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Belum ada santri aktif')
            ->assertDontSee('Isi absensi');
    }

    /**
     * @return array{ketua: User, ustaz: User, santri: SantriProfile}
     */
    private function fixture(string $ustazRole = Role::KetuaPengajar): array
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole($ustazRole, ['username' => 'ustaz1']);
        $santri = $this->makeSantri('2026801', 'Ahmad');

        return compact('ketua', 'ustaz', 'santri');
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
