<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SetoranStatus;
use App\Models\AcademicYear;
use App\Models\HafalanSetoran;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\SantriProfile;
use App\Models\User;
use App\Services\HafalanProgress;
use App\Support\Role;
use Database\Seeders\QuranSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HafalanProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(QuranSeeder::class);
    }

    public function test_fatihah_lancar_raises_juz_one_and_duplicates_or_ulang_do_not(): void
    {
        $fx = $this->progressFixture();
        $service = app(HafalanProgress::class);

        $this->storeSetoran($fx, $fx['santri'], 1, 1, 7, SetoranStatus::Lancar);

        $first = $service->forSantri($fx['santri'], $fx['year']);
        $this->assertSame(7, $first->uniqueAyahCount);
        $this->assertSame(7, $first->juz(1)->lancarCount);
        $this->assertSame(4.73, $first->juz(1)->percent);
        $this->assertSame(0.11, $first->totalPercent);
        $this->assertSame(0, $first->juz(2)->lancarCount);
        $this->assertSame(1, $first->currentJuz()?->number);
        $this->assertSame(0, $first->completedJuzCount());

        $this->storeSetoran($fx, $fx['santri'], 1, 1, 7, SetoranStatus::Lancar, now()->addDay()->toDateString());
        $second = $service->forSantri($fx['santri'], $fx['year']);
        $this->assertSame(7, $second->uniqueAyahCount);
        $this->assertSame($first->totalPercent, $second->totalPercent);
        $this->assertSame($first->juz(1)->percent, $second->juz(1)->percent);

        $this->storeSetoran($fx, $fx['santri'], 2, 1, 5, SetoranStatus::Ulang);
        $third = $service->forSantri($fx['santri'], $fx['year']);
        $this->assertSame(7, $third->uniqueAyahCount);
        $this->assertSame($first->totalPercent, $third->totalPercent);
    }

    public function test_overlapping_lancar_ranges_are_unioned(): void
    {
        $fx = $this->progressFixture();
        $service = app(HafalanProgress::class);

        $this->storeSetoran($fx, $fx['santri'], 1, 1, 3, SetoranStatus::Lancar);
        $this->storeSetoran($fx, $fx['santri'], 1, 3, 7, SetoranStatus::Lancar, now()->addDay()->toDateString());

        $result = $service->forSantri($fx['santri'], $fx['year']);
        $this->assertSame(7, $result->uniqueAyahCount);
        $this->assertSame(7, $result->juz(1)->lancarCount);
    }

    /**
     * @return array{year: AcademicYear, ustaz: User, halaqah: Halaqah, santri: SantriProfile}
     */
    private function progressFixture(): array
    {
        $ustaz = $this->userWithRole(Role::Ustaz);
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
        $santri = $this->makeSantri('2026001', 'Ahmad Fauzi');
        HalaqahMember::query()->create([
            'halaqah_id' => $halaqah->id,
            'santri_id' => $santri->id,
            'academic_year_id' => $year->id,
            'started_at' => '2026-07-01',
        ]);

        return compact('year', 'ustaz', 'halaqah', 'santri');
    }

    /**
     * @param  array{year: AcademicYear, ustaz: User, halaqah: Halaqah, santri: SantriProfile}  $fx
     */
    private function storeSetoran(
        array $fx,
        SantriProfile $santri,
        int $surah,
        int $start,
        int $end,
        SetoranStatus $status,
        ?string $date = null,
    ): HafalanSetoran {
        return HafalanSetoran::query()->create([
            'santri_id' => $santri->id,
            'halaqah_id' => $fx['halaqah']->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'academic_year_id' => $fx['year']->id,
            'quran_surah_id' => $surah,
            'setoran_date' => $date ?? now()->toDateString(),
            'ayah_start' => $start,
            'ayah_end' => $end,
            'status' => $status,
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
