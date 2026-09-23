<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\Holiday;
use App\Models\SantriProfile;
use App\Models\Schedule;
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
        $fx = $this->fixture(3);

        $this->assertSame(0, AttendanceSession::query()->count());

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Isi absensi')
            ->assertDontSee('Buka sesi');

        $this->assertSame(1, AttendanceSession::query()->count());
        $session = AttendanceSession::query()->first();
        $this->assertSame($fx['schedule']->id, $session->schedule_id);
        $this->assertSame('2026-09-23', $session->session_date->toDateString());
        $this->assertSame($fx['ustaz']->id, $session->opened_by_user_id);
    }

    public function test_attendance_index_skips_session_on_holiday(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $fx = $this->fixture(3);

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
        $fx = $this->fixture(6);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Hari libur (Sabtu/Minggu)')
            ->assertDontSee('Buka sesi');

        $this->assertSame(0, AttendanceSession::query()->count());
    }

    public function test_dashboard_auto_creates_session_and_shows_isi_absensi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $fx = $this->fixture(3);

        $this->actingAs($fx['ketua'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Isi absensi')
            ->assertSee('Sesi terbuka')
            ->assertDontSee('Buka sesi')
            ->assertDontSee('Belum dibuka');

        $this->assertSame(1, AttendanceSession::query()->count());
    }

    public function test_attendance_surfaces_use_date_without_time_or_halaqah_title(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $fx = $this->fixture(3);

        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.index'))->assertOk();
        $session = AttendanceSession::query()->firstOrFail();

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Jadwal hari ini')
            ->assertSee('Rabu, 23 September 2026')
            ->assertDontSee('Halaqah Tahfidz A')
            ->assertDontSee('07:00')
            ->assertDontSee('Masjid Utama');

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.show', $session))
            ->assertOk()
            ->assertSee('Rabu, 23 September 2026')
            ->assertDontSee('Halaqah Tahfidz A')
            ->assertDontSee('07:00')
            ->assertDontSee('Masjid Utama');
    }

    public function test_pengajar_without_halaqah_sees_assignment_message(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $pengajar = User::factory()->create(['username' => 'lonely']);
        $pengajar->assignRole(Role::Pengajar);

        $this->actingAs($pengajar)
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Belum ada kelas yang ditugaskan kepada Anda')
            ->assertDontSee('Tidak ada jadwal untuk');
    }

    public function test_pengajar_sees_same_attendance_ui_as_ketua_pengajar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $fx = $this->fixture(3, Role::Pengajar);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Jadwal hari ini')
            ->assertSee('Rabu, 23 September 2026')
            ->assertSee('Isi absensi')
            ->assertDontSee('Halaqah Tahfidz A')
            ->assertDontSee('Buka sesi')
            ->assertDontSee('Masjid Utama');

        $this->actingAs($fx['ustaz'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Jadwal hari ini')
            ->assertSee('Rabu, 23 September 2026')
            ->assertSee('Isi absensi')
            ->assertDontSee('Masjid Utama');
    }

    public function test_attendance_history_excludes_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 10:00:00'));
        $fx = $this->fixture(3);

        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.index'))->assertOk();

        AttendanceSession::query()->create([
            'schedule_id' => $fx['schedule']->id,
            'session_date' => '2026-09-22',
            'opened_by_user_id' => $fx['ustaz']->id,
        ]);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Selasa, 22 September 2026')
            ->assertSee('Riwayat sesi');
    }

    /**
     * @return array{ketua: User, ustaz: User, schedule: Schedule, halaqah: Halaqah}
     */
    private function fixture(int $dayOfWeek, string $ustazRole = Role::KetuaPengajar): array
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);
        $ustaz = User::factory()->create(['username' => 'ustaz1']);
        $ustaz->assignRole($ustazRole);

        $year = AcademicYear::query()->create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $halaqah = Halaqah::query()->create([
            'academic_year_id' => $year->id,
            'ustaz_user_id' => $ustaz->id,
            'name' => 'Halaqah Tahfidz A',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['name' => 'Ahmad', 'username' => '2026801']);
        $user->assignRole(Role::Santri);
        $santri = SantriProfile::query()->create([
            'user_id' => $user->id,
            'nis' => '2026801',
            'gender' => Gender::LakiLaki,
            'status' => SantriStatus::Aktif,
        ]);
        HalaqahMember::query()->create([
            'halaqah_id' => $halaqah->id,
            'santri_id' => $santri->id,
            'academic_year_id' => $year->id,
            'started_at' => '2026-07-01',
        ]);

        $schedule = Schedule::query()->create([
            'halaqah_id' => $halaqah->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => '15:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        return compact('ketua', 'ustaz', 'schedule', 'halaqah');
    }
}
