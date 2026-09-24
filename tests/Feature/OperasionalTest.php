<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SetoranStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\QuranSurah;
use App\Models\SantriProfile;
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
        $this->travelTo('2026-09-23 10:00:00'); // Wednesday
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

    public function test_pengajar_can_absen_all_active_santri(): void
    {
        $fx = $this->opsFixture(Role::Pengajar);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Isi absensi')
            ->assertDontSee('Belum ada kelas yang ditugaskan');

        $session = AttendanceSession::query()->firstOrFail();

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.attendance.show', $session))
            ->assertOk()
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Hasan Basri')
            ->assertSee('Yusuf Maulana');
    }

    public function test_another_pengajar_can_open_and_save_same_day_session(): void
    {
        $fx = $this->opsFixture();
        $other = $this->userWithRole(Role::Pengajar, ['username' => 'pengajar2', 'name' => 'Pengajar Dua']);

        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.index'))->assertOk();
        $session = AttendanceSession::query()->firstOrFail();

        $this->actingAs($other)
            ->get(route('ops.attendance.show', $session))
            ->assertOk()
            ->assertSee('Ahmad Fauzi');

        $this->actingAs($other)
            ->put(route('ops.attendance.update', $session), [
                'rows' => [
                    $fx['santri'][0]->id => ['status' => AttendanceStatus::Hadir->value],
                    $fx['santri'][1]->id => ['status' => AttendanceStatus::Izin->value],
                    $fx['santri'][2]->id => ['status' => AttendanceStatus::Alfa->value],
                ],
            ])
            ->assertRedirect(route('ops.setoran.create', ['sesi' => $session->id]));
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

    public function test_setoran_index_defaults_to_today_with_summary_cards(): void
    {
        $this->travelTo('2026-09-07 08:00:00');

        $fx = $this->opsFixture();

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index'))
            ->assertOk()
            ->assertSee('Santri aktif')
            ->assertSee('Hafalan')
            ->assertSee('Bacaan')
            ->assertSee('patokan hitungan')
            ->assertSee('3 belum')
            ->assertSee('Hari ini')
            ->assertSee('name="date_from"', false)
            ->assertSee('value="2026-09-07"', false)
            ->assertDontSee('Minggu ini')
            ->assertDontSee('Filter lain');
    }

    public function test_setoran_index_counts_active_santri_per_category(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();
        $this->makeSetoran($fx, $fx['santri'][0], SetoranStatus::Lulus);
        $this->makeSetoran($fx, $fx['santri'][1], SetoranStatus::Mengulang);
        $cuti = $this->makeSantri('2026004', 'Ali Cuti');
        $cuti->update(['status' => SantriStatus::Cuti]);
        $this->makeSetoran($fx, $cuti, SetoranStatus::Lulus, category: 'hafalan', subtype: 'doa', doaName: 'Doa tidur');

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index'))
            ->assertOk()
            ->assertSee('>Ahmad Fauzi</p>', false)
            ->assertSee('>Hasan Basri</p>', false)
            ->assertSee('>Ali Cuti</p>', false)
            ->assertSee('1 belum')
            ->assertSee('1 perlu diulang')
            ->assertSee('3 belum')
            ->assertDontSee('>Yusuf Maulana</p>', false)
            ->assertViewHas('summary', [
                'active' => 3,
                'hafalan' => ['sudah' => 0, 'belum' => 3, 'ulang' => 0],
                'bacaan' => ['sudah' => 2, 'belum' => 1, 'ulang' => 1],
            ]);
    }

    public function test_setoran_index_selected_range_hides_rows_outside_it(): void
    {
        $this->travelTo('2026-09-07 08:00:00');

        $fx = $this->opsFixture();
        $this->seedFatihah();
        $this->makeSetoran($fx, $fx['santri'][0], SetoranStatus::Lulus, '2026-09-01');
        $this->makeSetoran($fx, $fx['santri'][1], SetoranStatus::Lulus, '2026-09-07');

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index'))
            ->assertOk()
            ->assertSee('>Hasan Basri</p>', false)
            ->assertDontSee('>Ahmad Fauzi</p>', false);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index', [
                'date_from' => '2026-09-01',
                'date_to' => '2026-09-07',
            ]))
            ->assertOk()
            ->assertSee('>Hasan Basri</p>', false)
            ->assertSee('>Ahmad Fauzi</p>', false)
            ->assertSee('7 September 2026')
            ->assertSee('Hari ini');
    }

    public function test_setoran_index_pages_past_twenty_rows(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();

        for ($ayah = 1; $ayah <= 21; $ayah++) {
            $this->makeSetoran($fx, $fx['santri'][0], SetoranStatus::Lulus, ayahEnd: $ayah);
        }

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index'))
            ->assertOk()
            ->assertSee('Al-Fatihah 1–20')
            ->assertDontSee('Al-Fatihah 1–21');

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Al-Fatihah 1–21')
            ->assertDontSee('Al-Fatihah 1–20');
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

    public function test_another_pengajar_can_open_setoran_for_shared_session(): void
    {
        $fx = $this->opsFixture();
        $session = $this->saveMixedAttendance($fx);
        $other = $this->userWithRole(Role::Pengajar, ['username' => 'ustaz2']);

        $this->actingAs($other)
            ->get(route('ops.setoran.create', ['sesi' => $session->id]))
            ->assertOk()
            ->assertSee('Ahmad Fauzi');
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

    public function test_ketua_can_open_global_day_session(): void
    {
        $fx = $this->opsFixture();

        $this->actingAs($fx['ketua'])
            ->get(route('ops.attendance.index'))
            ->assertOk()
            ->assertSee('Isi absensi');

        $this->assertTrue(AttendanceSession::query()->exists());
        $this->assertSame(1, AttendanceSession::query()->whereDate('session_date', now()->toDateString())->count());
    }

    public function test_super_admin_cannot_open_ops_menu(): void
    {
        $admin = $this->userWithRole(Role::SuperAdmin);

        $this->actingAs($admin)
            ->get(route('ops.attendance.index'))
            ->assertForbidden();
    }

    /**
     * @param  array{ustaz: User}  $fx
     */
    private function makeSetoran(
        array $fx,
        SantriProfile $santri,
        SetoranStatus $status,
        ?string $date = null,
        int $ayahEnd = 7,
        string $category = 'bacaan',
        string $subtype = 'alquran',
        ?string $doaName = null,
    ): HafalanSetoran {
        $isQuran = $subtype !== 'doa';

        return HafalanSetoran::query()->create([
            'santri_id' => $santri->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'activity_type' => $category === 'hafalan' ? 'hafalan' : 'ngaji',
            'category' => $category,
            'subtype' => $subtype,
            'doa_name' => $doaName,
            'quran_surah_id' => $isQuran ? 1 : null,
            'setoran_date' => $date ?? now()->toDateString(),
            'ayah_start' => $isQuran ? 1 : null,
            'ayah_end' => $isQuran ? $ayahEnd : null,
            'status' => $status,
        ]);
    }

    /**
     * @param  array{ustaz: User, santri: list<SantriProfile>}  $fx
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
     * @return array{ketua: User, ustaz: User, santri: list<SantriProfile>}
     */
    private function opsFixture(string $ustazRole = Role::KetuaPengajar): array
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole($ustazRole, ['username' => 'ustaz1']);

        $santri = [];
        foreach (['2026001' => 'Ahmad Fauzi', '2026002' => 'Hasan Basri', '2026003' => 'Yusuf Maulana'] as $nis => $name) {
            $santri[] = $this->makeSantri($nis, $name);
        }

        return compact('ketua', 'ustaz', 'santri');
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
