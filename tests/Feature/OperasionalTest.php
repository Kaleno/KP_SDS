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

    public function test_ustaz_auto_session_allows_saving_mixed_status(): void
    {
        $fx = $this->opsFixture();

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Isi absensi');

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
            ->assertRedirect(route('ops.setoran.create', ['sesi' => $session->id]));

        $this->assertSame(1, Attendance::query()->where('status', AttendanceStatus::Alfa)->count());
        $this->assertSame(1, Attendance::query()->where('status', AttendanceStatus::Izin)->count());
    }

    public function test_other_ustaz_cannot_see_session(): void
    {
        $fx = $this->opsFixture();
        $outsider = $this->userWithRole(Role::Pengajar, ['username' => 'ustaz2']);

        $this->actingAs($outsider)
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertDontSee('Isi absensi');

        $session = AttendanceSession::query()->create([
            'schedule_id' => $fx['schedule']->id,
            'session_date' => now()->toDateString(),
            'opened_by_user_id' => $fx['ustaz']->id,
        ]);

        $this->actingAs($outsider)
            ->get(route('ops.attendance.show', $session))
            ->assertForbidden();
    }

    public function test_ketua_pengajar_can_open_another_pengajar_session(): void
    {
        $fx = $this->opsFixture();
        $other = $this->userWithRole(Role::Pengajar, ['username' => 'pengajar2', 'name' => 'Pengajar Dua']);
        $otherHalaqah = Halaqah::query()->create([
            'academic_year_id' => $fx['halaqah']->academic_year_id,
            'ustaz_user_id' => $other->id,
            'name' => 'Kelas Pengajar Lain',
            'is_active' => true,
        ]);
        $otherSchedule = Schedule::query()->create([
            'halaqah_id' => $otherHalaqah->id,
            'day_of_week' => now()->isoWeekday(),
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'is_active' => true,
        ]);
        $session = AttendanceSession::query()->create([
            'schedule_id' => $otherSchedule->id,
            'session_date' => now()->toDateString(),
            'opened_by_user_id' => $other->id,
        ]);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Isi absensi');

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.show', $session))
            ->assertOk();
    }

    public function test_attendance_session_page_renders_status_choice_styles(): void
    {
        $fx = $this->opsFixture();

        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.index'));
        $session = AttendanceSession::query()->first();

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.show', $session))
            ->assertOk()
            ->assertSee('attendance-picks', false)
            ->assertSee('ui-choice-compact', false)
            ->assertSee('ui-choice-hadir', false)
            ->assertSee('ui-choice-izin', false)
            ->assertSee('ui-choice-sakit', false)
            ->assertSee('ui-choice-alfa', false)
            ->assertSee('Hadir')
            ->assertSee('Izin')
            ->assertSee('Sakit')
            ->assertSee('Alfa')
            ->assertSee('+ Catatan');
    }

    public function test_setoran_index_renders_quick_filter_chips(): void
    {
        $fx = $this->opsFixture();

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index'))
            ->assertOk()
            ->assertSee('Hari ini')
            ->assertSee('Minggu ini')
            ->assertSee('Bulan ini')
            ->assertSee('Lulus')
            ->assertSee('Mengulang')
            ->assertSee('Filter lain')
            ->assertSee('aria-label="Rentang waktu"', false)
            ->assertSee('aria-label="Status setoran"', false)
            ->assertDontSee('Reset');
    }

    public function test_setoran_index_status_filter_hides_other_rows(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();
        $this->makeSetoran($fx, $fx['santri'][0], SetoranStatus::Lulus);
        $this->makeSetoran($fx, $fx['santri'][1], SetoranStatus::Mengulang);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index', ['status' => SetoranStatus::Mengulang->value]))
            ->assertOk()
            ->assertSee('>Hasan Basri</p>', false)
            ->assertDontSee('>Ahmad Fauzi</p>', false)
            ->assertSee('Reset');
    }

    public function test_setoran_index_today_filter_hides_older_rows(): void
    {
        $this->travelTo('2026-09-07 08:00:00');

        $fx = $this->opsFixture();
        $this->seedFatihah();
        $this->makeSetoran($fx, $fx['santri'][0], SetoranStatus::Lulus, '2026-09-01');
        $this->makeSetoran($fx, $fx['santri'][1], SetoranStatus::Lulus, '2026-09-07');

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index', [
                'date_from' => '2026-09-07',
                'date_to' => '2026-09-07',
            ]))
            ->assertOk()
            ->assertSee('>Hasan Basri</p>', false)
            ->assertDontSee('>Ahmad Fauzi</p>', false);
    }

    public function test_setoran_create_embeds_next_ayah_after_last_setoran(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();
        $this->makeSetoran($fx, $fx['santri'][0], SetoranStatus::Lulus, ayahEnd: 5);
        $this->makeSetoran($fx, $fx['santri'][1], SetoranStatus::Lulus, ayahEnd: 2);

        $response = $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.create'))
            ->assertOk()
            ->assertSee('nextAyahBySantri', false)
            ->assertSee('fillAyahStart', false);

        $map = $response->viewData('nextAyahBySantri');
        $this->assertSame(6, $map[$fx['santri'][0]->id][1]);
        $this->assertSame(3, $map[$fx['santri'][1]->id][1]);
        $this->assertArrayNotHasKey($fx['santri'][2]->id, $map);
    }

    public function test_setoran_create_uses_latest_setoran_for_next_ayah(): void
    {
        $this->travelTo('2026-09-07 08:00:00');

        $fx = $this->opsFixture();
        $this->seedFatihah();
        $this->makeSetoran($fx, $fx['santri'][0], SetoranStatus::Lulus, '2026-09-01', ayahEnd: 3);
        $this->makeSetoran($fx, $fx['santri'][0], SetoranStatus::Lulus, '2026-09-07', ayahEnd: 5);

        $map = $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.create'))
            ->assertOk()
            ->viewData('nextAyahBySantri');

        $this->assertSame(6, $map[$fx['santri'][0]->id][1]);
    }

    public function test_setoran_create_restarts_ayah_when_surah_is_complete(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();
        $this->makeSetoran($fx, $fx['santri'][0], SetoranStatus::Lulus);

        $map = $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.create'))
            ->assertOk()
            ->viewData('nextAyahBySantri');

        $this->assertSame(1, $map[$fx['santri'][0]->id][1]);
    }

    public function test_ustaz_can_store_setoran_and_correct_it(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri'][0]->id,
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lulus->value,
            'note' => 'Al-Fatihah',
        ])->assertRedirect(route('ops.setoran.index'));

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri'][1]->id,
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Mengulang->value,
        ])->assertRedirect();

        $setoran = HafalanSetoran::query()->where('santri_id', $fx['santri'][0]->id)->first();
        $this->assertSame(SetoranStatus::Lulus, $setoran->status);

        $this->actingAs($fx['ustaz'])->put(route('ops.setoran.update', $setoran), [
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 5,
            'status' => SetoranStatus::Mengulang->value,
            'correction_note' => 'Salah rentang ayat',
        ])->assertRedirect(route('ops.setoran.index'));

        $this->assertSame(SetoranStatus::Mengulang, $setoran->fresh()->status);
        $this->assertSame('Salah rentang ayat', $setoran->fresh()->correction_note);
    }

    public function test_setoran_rejects_ayah_beyond_surah(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri'][0]->id,
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 8,
            'status' => SetoranStatus::Lulus->value,
        ])->assertSessionHasErrors('ayah_end');
    }

    public function test_setoran_create_with_session_lists_only_hadir_waiting(): void
    {
        $fx = $this->opsFixture();
        $session = $this->saveMixedAttendance($fx);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.create', ['sesi' => $session->id]))
            ->assertOk()
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Cari nomor, nama surat, atau juz')
            ->assertDontSee('Yusuf Maulana')
            ->assertDontSee('Hasan Basri');
    }

    public function test_other_ustaz_cannot_open_setoran_for_foreign_session(): void
    {
        $fx = $this->opsFixture();
        $session = $this->saveMixedAttendance($fx);
        $outsider = $this->userWithRole(Role::Pengajar, ['username' => 'ustaz2']);

        $this->actingAs($outsider)
            ->get(route('ops.setoran.create', ['sesi' => $session->id]))
            ->assertForbidden();
    }

    public function test_setoran_with_session_rejects_absent_santri(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();
        $session = $this->saveMixedAttendance($fx);

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri'][2]->id,
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lulus->value,
            'sesi' => $session->id,
        ])->assertSessionHasErrors('santri_id');

        $this->assertSame(0, HafalanSetoran::query()->count());
    }

    public function test_setoran_with_session_continues_until_present_santri_are_recorded(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();

        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.index'));
        $session = AttendanceSession::query()->first();

        $this->actingAs($fx['ustaz'])->put(route('ops.attendance.update', $session), [
            'rows' => [
                $fx['santri'][0]->id => ['status' => AttendanceStatus::Hadir->value],
                $fx['santri'][1]->id => ['status' => AttendanceStatus::Hadir->value],
                $fx['santri'][2]->id => ['status' => AttendanceStatus::Alfa->value],
            ],
        ])->assertRedirect(route('ops.setoran.create', ['sesi' => $session->id]));

        $payload = [
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lulus->value,
            'sesi' => $session->id,
        ];

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            ...$payload,
            'santri_id' => $fx['santri'][0]->id,
        ])->assertRedirect(route('ops.setoran.create', ['sesi' => $session->id]));

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.create', ['sesi' => $session->id]))
            ->assertOk()
            ->assertSee('Hasan Basri')
            ->assertSee('Ahmad Fauzi');

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            ...$payload,
            'santri_id' => $fx['santri'][1]->id,
        ])->assertRedirect(route('ops.setoran.create', ['sesi' => $session->id]));

        $this->assertSame(2, HafalanSetoran::query()->count());
    }

    public function test_ketua_can_open_any_halaqah_session(): void
    {
        $fx = $this->opsFixture();

        $this->actingAs($fx['ketua'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Isi absensi');

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
     * @param  array{ustaz: User, halaqah: Halaqah}  $fx
     */
    private function makeSetoran(array $fx, SantriProfile $santri, SetoranStatus $status, ?string $date = null, int $ayahEnd = 7): HafalanSetoran
    {
        return HafalanSetoran::query()->create([
            'santri_id' => $santri->id,
            'halaqah_id' => $fx['halaqah']->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'academic_year_id' => $fx['halaqah']->academic_year_id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => $date ?? now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => $ayahEnd,
            'status' => $status,
        ]);
    }

    /**
     * @param  array{ustaz: User, schedule: Schedule, santri: list<SantriProfile>}  $fx
     */
    private function saveMixedAttendance(array $fx): AttendanceSession
    {
        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.index'));
        $session = AttendanceSession::query()->first();

        $rows = [];
        foreach ($fx['santri'] as $index => $santri) {
            $status = [AttendanceStatus::Hadir, AttendanceStatus::Izin, AttendanceStatus::Alfa][$index];
            $rows[$santri->id] = ['status' => $status->value];
        }

        $this->actingAs($fx['ustaz'])
            ->put(route('ops.attendance.update', $session), ['rows' => $rows])
            ->assertRedirect(route('ops.setoran.create', ['sesi' => $session->id]));

        return $session;
    }

    /**
     * @return array{ketua: User, ustaz: User, halaqah: Halaqah, schedule: Schedule, santri: list<SantriProfile>}
     */
    private function opsFixture(): array
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole(Role::KetuaPengajar, ['username' => 'ustaz1']);
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
