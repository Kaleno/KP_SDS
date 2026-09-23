<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SetoranStatus;
use App\Enums\SetoranSubtype;
use App\Models\HafalanSetoran;
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

        $this->storeSetoran($fx, $fx['santri'], 1, 1, 7, SetoranStatus::Lulus);

        $first = $service->forSantri($fx['santri'], SetoranSubtype::Alquran);
        $this->assertSame(7, $first->uniqueAyahCount);
        $this->assertSame(7, $first->juz(1)->lancarCount);
        $this->assertSame(4.73, $first->juz(1)->percent);
        $this->assertSame(0.11, $first->totalPercent);
        $this->assertSame(0, $first->juz(2)->lancarCount);
        $this->assertSame(1, $first->currentJuz()?->number);
        $this->assertSame(0, $first->completedJuzCount());

        $this->storeSetoran($fx, $fx['santri'], 1, 1, 7, SetoranStatus::Lulus, now()->addDay()->toDateString());
        $second = $service->forSantri($fx['santri'], SetoranSubtype::Alquran);
        $this->assertSame(7, $second->uniqueAyahCount);
        $this->assertSame($first->totalPercent, $second->totalPercent);
        $this->assertSame($first->juz(1)->percent, $second->juz(1)->percent);

        $this->storeSetoran($fx, $fx['santri'], 2, 1, 5, SetoranStatus::Mengulang);
        $third = $service->forSantri($fx['santri'], SetoranSubtype::Alquran);
        $this->assertSame(7, $third->uniqueAyahCount);
        $this->assertSame($first->totalPercent, $third->totalPercent);
    }

    public function test_overlapping_lancar_ranges_are_unioned(): void
    {
        $fx = $this->progressFixture();
        $service = app(HafalanProgress::class);

        $this->storeSetoran($fx, $fx['santri'], 1, 1, 3, SetoranStatus::Lulus);
        $this->storeSetoran($fx, $fx['santri'], 1, 3, 7, SetoranStatus::Lulus, now()->addDay()->toDateString());

        $result = $service->forSantri($fx['santri'], SetoranSubtype::Alquran);
        $this->assertSame(7, $result->uniqueAyahCount);
        $this->assertSame(7, $result->juz(1)->lancarCount);
    }

    /**
     * @return array{ustaz: User, santri: SantriProfile}
     */
    private function progressFixture(): array
    {
        $ustaz = $this->userWithRole(Role::KetuaPengajar);
        $santri = $this->makeSantri('2026001', 'Ahmad Fauzi');

        return compact('ustaz', 'santri');
    }

    /**
     * @param  array{ustaz: User}  $fx
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
            'ustaz_user_id' => $fx['ustaz']->id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => SetoranSubtype::Alquran,
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
