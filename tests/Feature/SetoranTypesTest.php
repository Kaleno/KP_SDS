<?php

namespace Tests\Feature;

use App\Enums\ActivityType;
use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Enums\SetoranStatus;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\QuranJuz;
use App\Models\QuranSurah;
use App\Models\SantriProfile;
use App\Models\Schedule;
use App\Models\User;
use App\Support\Role;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetoranTypesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_teacher_can_store_bacaan_iqro_even_for_alquran_track(): void
    {
        $fx = $this->fixture(SantriTrack::Alquran);

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri']->id,
            'setoran_date' => now()->toDateString(),
            'category' => 'bacaan',
            'subtype' => 'iqro',
            'iqro_level' => 3,
            'iqro_page' => 10,
            'status' => SetoranStatus::Lulus->value,
        ])->assertRedirect(route('ops.setoran.index'));

        $this->assertDatabaseHas('hafalan_setoran', [
            'santri_id' => $fx['santri']->id,
            'category' => 'bacaan',
            'subtype' => 'iqro',
            'activity_type' => ActivityType::Ngaji->value,
            'iqro_level' => 3,
            'iqro_page' => 10,
        ]);
    }

    public function test_teacher_can_store_hafalan_doa(): void
    {
        $fx = $this->fixture(SantriTrack::Iqro);

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri']->id,
            'setoran_date' => now()->toDateString(),
            'category' => 'hafalan',
            'subtype' => 'doa',
            'doa_name' => 'Doa sebelum makan',
            'status' => SetoranStatus::Lulus->value,
            'note' => 'Lancar',
        ])->assertRedirect(route('ops.setoran.index'));

        $row = HafalanSetoran::query()->first();
        $this->assertSame('Doa sebelum makan', $row->doa_name);
        $this->assertSame(ActivityType::Hafalan, $row->activity_type);
        $this->assertSame('Doa sebelum makan', $row->passageLabel());

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index'))
            ->assertOk()
            ->assertSee('Hafalan · Doa')
            ->assertSee('Doa sebelum makan');
    }

    public function test_hafalan_juz30_rejects_surah_outside_juz(): void
    {
        $fx = $this->fixture(SantriTrack::Alquran);
        $this->seedSurah(1, 'Al-Fatihah', 7);
        $this->seedSurah(78, 'An-Naba', 40);
        $this->seedSurah(114, 'An-Nas', 6);
        $this->seedJuz30();

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri']->id,
            'setoran_date' => now()->toDateString(),
            'category' => 'hafalan',
            'subtype' => 'juz30',
            'quran_surah_id' => 1,
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lulus->value,
        ])->assertSessionHasErrors('quran_surah_id');
    }

    public function test_hafalan_juz30_accepts_surah_in_juz(): void
    {
        $fx = $this->fixture(SantriTrack::Alquran);
        $this->seedSurah(78, 'An-Naba', 40);
        $this->seedSurah(114, 'An-Nas', 6);
        $this->seedJuz30();

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri']->id,
            'setoran_date' => now()->toDateString(),
            'category' => 'hafalan',
            'subtype' => 'juz30',
            'quran_surah_id' => 78,
            'ayah_start' => 1,
            'ayah_end' => 5,
            'status' => SetoranStatus::Lulus->value,
        ])->assertRedirect(route('ops.setoran.index'));

        $this->assertDatabaseHas('hafalan_setoran', [
            'santri_id' => $fx['santri']->id,
            'category' => 'hafalan',
            'subtype' => 'juz30',
            'activity_type' => ActivityType::Hafalan->value,
            'quran_surah_id' => 78,
        ]);
    }

    public function test_friday_no_longer_forces_juz30_for_bacaan_alquran(): void
    {
        $fx = $this->fixture(SantriTrack::Alquran);
        $this->seedSurah(1, 'Al-Fatihah', 7);

        $friday = now()->next(Carbon::FRIDAY);

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri']->id,
            'setoran_date' => $friday->toDateString(),
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lulus->value,
        ])->assertRedirect(route('ops.setoran.index'));

        $this->assertDatabaseHas('hafalan_setoran', [
            'santri_id' => $fx['santri']->id,
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'activity_type' => ActivityType::Ngaji->value,
        ]);
    }

    public function test_session_allows_multiple_setoran_for_same_santri(): void
    {
        $fx = $this->fixture(SantriTrack::Alquran);
        $this->seedSurah(1, 'Al-Fatihah', 7);

        $this->actingAs($fx['ustaz'])->get(route('ops.attendance.index'));
        $session = AttendanceSession::query()->first();
        $this->actingAs($fx['ustaz'])->put(route('ops.attendance.update', $session), [
            'rows' => [
                $fx['santri']->id => ['status' => AttendanceStatus::Hadir->value],
            ],
        ]);

        $base = [
            'santri_id' => $fx['santri']->id,
            'setoran_date' => now()->toDateString(),
            'sesi' => $session->id,
            'status' => SetoranStatus::Lulus->value,
        ];

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            ...$base,
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'ayah_start' => 1,
            'ayah_end' => 3,
        ])->assertRedirect(route('ops.setoran.create', ['sesi' => $session->id]));

        $this->actingAs($fx['ustaz'])->post(route('ops.setoran.store'), [
            ...$base,
            'category' => 'hafalan',
            'subtype' => 'doa',
            'doa_name' => 'Doa masuk masjid',
        ])->assertRedirect(route('ops.setoran.create', ['sesi' => $session->id]));

        $this->assertSame(2, HafalanSetoran::query()->where('santri_id', $fx['santri']->id)->count());
    }

    public function test_create_form_continues_iqro_and_alquran_from_last_setoran(): void
    {
        $fx = $this->fixture(SantriTrack::Alquran);
        $this->seedSurah(1, 'Al-Fatihah', 7);
        $this->seedSurah(2, 'Al-Baqarah', 286);
        QuranJuz::query()->create([
            'number' => 1,
            'start_surah_id' => 1,
            'end_surah_id' => 2,
            'start_ayah' => 1,
            'end_ayah' => 141,
        ]);

        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri']->id,
            'halaqah_id' => $fx['halaqah']->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'academic_year_id' => $fx['halaqah']->academic_year_id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'iqro',
            'iqro_level' => 2,
            'iqro_page' => 10,
            'setoran_date' => now()->subDay()->toDateString(),
            'status' => SetoranStatus::Lulus,
        ]);

        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri']->id,
            'halaqah_id' => $fx['halaqah']->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'academic_year_id' => $fx['halaqah']->academic_year_id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'ayah_start' => 1,
            'ayah_end' => 7,
            'setoran_date' => now()->subDay()->toDateString(),
            'status' => SetoranStatus::Lulus,
        ]);

        $continue = $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.create'))
            ->assertOk()
            ->assertSee('Filter Juz')
            ->assertSee('Juz 1')
            ->viewData('continueBySantri');

        $this->assertSame(2, $continue[$fx['santri']->id]['iqro']['iqro_level']);
        $this->assertSame(11, $continue[$fx['santri']->id]['iqro']['iqro_page']);
        $this->assertSame(2, $continue[$fx['santri']->id]['alquran']['quran_surah_id']);
        $this->assertSame(1, $continue[$fx['santri']->id]['alquran']['ayah_start']);
        $this->assertSame(1, $continue[$fx['santri']->id]['alquran']['juz']);
    }

    public function test_create_form_continues_hafalan_juz30_from_last_setoran(): void
    {
        $fx = $this->fixture(SantriTrack::Alquran);
        $this->seedSurah(78, 'An-Naba', 40);
        $this->seedSurah(114, 'An-Nas', 6);
        $this->seedJuz30();

        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri']->id,
            'halaqah_id' => $fx['halaqah']->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'academic_year_id' => $fx['halaqah']->academic_year_id,
            'activity_type' => 'hafalan',
            'category' => 'hafalan',
            'subtype' => 'juz30',
            'quran_surah_id' => 78,
            'ayah_start' => 1,
            'ayah_end' => 10,
            'setoran_date' => now()->subDay()->toDateString(),
            'status' => SetoranStatus::Lulus,
        ]);

        $continue = $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.create'))
            ->assertOk()
            ->viewData('continueBySantri');

        $this->assertSame(78, $continue[$fx['santri']->id]['juz30']['quran_surah_id']);
        $this->assertSame(11, $continue[$fx['santri']->id]['juz30']['ayah_start']);
    }

    public function test_ketua_ketua_pengajar_and_pengajar_can_use_new_setoran_types(): void
    {
        $fx = $this->fixture(SantriTrack::Alquran);
        $this->seedSurah(1, 'Al-Fatihah', 7);

        $ketua = User::factory()->create(['username' => 'ketua_dkm']);
        $ketua->assignRole(Role::Ketua);

        $ketuaPengajar = User::factory()->create(['username' => 'ketua_pengajar']);
        $ketuaPengajar->assignRole(Role::KetuaPengajar);

        $roles = [
            'ketua' => $ketua,
            'ketua_pengajar' => $ketuaPengajar,
            'pengajar' => $fx['ustaz'],
        ];

        foreach ($roles as $label => $actor) {
            $this->actingAs($actor)
                ->get(route('ops.setoran.create'))
                ->assertOk()
                ->assertSee('Jenis setoran')
                ->assertSee('Bacaan')
                ->assertSee('Hafalan');

            $this->actingAs($actor)->post(route('ops.setoran.store'), [
                'santri_id' => $fx['santri']->id,
                'setoran_date' => now()->toDateString(),
                'category' => 'hafalan',
                'subtype' => 'doa',
                'doa_name' => 'Doa oleh '.$label,
                'status' => SetoranStatus::Lulus->value,
            ])->assertRedirect(route('ops.setoran.index'));

            $this->actingAs($actor)->post(route('ops.setoran.store'), [
                'santri_id' => $fx['santri']->id,
                'setoran_date' => now()->toDateString(),
                'category' => 'bacaan',
                'subtype' => 'alquran',
                'quran_surah_id' => 1,
                'ayah_start' => 1,
                'ayah_end' => 3,
                'status' => SetoranStatus::Lulus->value,
                'note' => 'Bacaan oleh '.$label,
            ])->assertRedirect(route('ops.setoran.index'));
        }

        $this->assertSame(3, HafalanSetoran::query()->where('subtype', 'doa')->count());
        $this->assertSame(3, HafalanSetoran::query()->where('subtype', 'alquran')->count());
        $this->assertTrue(
            HafalanSetoran::query()->where('ustaz_user_id', $ketua->id)->exists()
            && HafalanSetoran::query()->where('ustaz_user_id', $ketuaPengajar->id)->exists()
            && HafalanSetoran::query()->where('ustaz_user_id', $fx['ustaz']->id)->exists(),
        );
    }

    /**
     * @return array{ustaz: User, halaqah: Halaqah, santri: SantriProfile}
     */
    private function fixture(SantriTrack $track): array
    {
        $ustaz = User::factory()->create(['username' => 'ustaz1']);
        $ustaz->assignRole(Role::Pengajar);

        $year = AcademicYear::query()->create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $halaqah = Halaqah::query()->create([
            'academic_year_id' => $year->id,
            'ustaz_user_id' => $ustaz->id,
            'name' => 'Halaqah A',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Ahmad Fauzi',
            'username' => '2026101',
            'email' => '2026101@santri.test',
        ]);
        $user->assignRole(Role::Santri);

        $santri = SantriProfile::query()->create([
            'user_id' => $user->id,
            'nis' => '2026101',
            'gender' => Gender::LakiLaki,
            'track' => $track,
            'iqro_level' => $track === SantriTrack::Iqro ? 1 : null,
            'status' => SantriStatus::Aktif,
        ]);

        HalaqahMember::query()->create([
            'halaqah_id' => $halaqah->id,
            'santri_id' => $santri->id,
            'academic_year_id' => $year->id,
            'started_at' => '2026-07-01',
        ]);

        Schedule::query()->create([
            'halaqah_id' => $halaqah->id,
            'day_of_week' => now()->isoWeekday(),
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'is_active' => true,
        ]);

        return compact('ustaz', 'halaqah', 'santri');
    }

    private function seedSurah(int $id, string $name, int $ayahCount): void
    {
        QuranSurah::query()->create([
            'id' => $id,
            'name_id' => $name,
            'name_ar' => $name,
            'ayah_count' => $ayahCount,
        ]);
    }

    private function seedJuz30(): void
    {
        QuranJuz::query()->create([
            'number' => 30,
            'start_surah_id' => 78,
            'end_surah_id' => 114,
            'start_ayah' => 1,
            'end_ayah' => 6,
        ]);
    }
}
