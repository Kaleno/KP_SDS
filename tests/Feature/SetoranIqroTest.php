<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Enums\SetoranStatus;
use App\Models\HafalanSetoran;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetoranIqroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->travelTo('2026-09-23 10:00:00');
    }

    public function test_ketua_can_store_iqro_setoran_without_surah(): void
    {
        $fx = $this->iqroFixture();

        $this->actingAs($fx['ketua'])->post(route('ops.setoran.store'), [
            'santri_id' => $fx['santri']->id,
            'setoran_date' => now()->toDateString(),
            'category' => 'bacaan',
            'subtype' => 'iqro',
            'iqro_level' => 2,
            'iqro_page' => 15,
            'status' => SetoranStatus::Lulus->value,
            'note' => 'Iqro 2',
        ])->assertRedirect(route('ops.setoran.index'));

        $this->assertDatabaseHas('hafalan_setoran', [
            'santri_id' => $fx['santri']->id,
            'iqro_level' => 2,
            'iqro_page' => 15,
            'quran_surah_id' => null,
            'ayah_start' => null,
            'ayah_end' => null,
        ]);

        $this->assertSame(2, $fx['santri']->fresh()->iqro_level);

        $this->actingAs($fx['ketua'])
            ->get(route('ops.setoran.index'))
            ->assertOk()
            ->assertSee('Iqro 2 hlm. 15');
    }

    public function test_ketua_can_correct_iqro_setoran(): void
    {
        $fx = $this->iqroFixture();

        $setoran = HafalanSetoran::query()->create([
            'santri_id' => $fx['santri']->id,
            'ustaz_user_id' => $fx['ketua']->id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'iqro',
            'iqro_level' => 1,
            'iqro_page' => 5,
            'quran_surah_id' => null,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => null,
            'ayah_end' => null,
            'status' => SetoranStatus::Lulus,
        ]);

        $this->actingAs($fx['ketua'])->put(route('ops.setoran.update', $setoran), [
            'setoran_date' => now()->toDateString(),
            'category' => 'bacaan',
            'subtype' => 'iqro',
            'iqro_level' => 1,
            'iqro_page' => 8,
            'status' => SetoranStatus::Mengulang->value,
            'correction_note' => 'Halaman salah',
        ])->assertRedirect(route('ops.setoran.index'));

        $this->assertSame(8, $setoran->fresh()->iqro_page);
        $this->assertSame(SetoranStatus::Mengulang, $setoran->fresh()->status);
    }

    /**
     * @return array{ketua: User, santri: SantriProfile}
     */
    private function iqroFixture(): array
    {
        $ketua = User::factory()->create(['username' => 'ketua']);
        $ketua->assignRole(Role::Ketua);

        $user = User::factory()->create([
            'name' => 'Siti Aisyah',
            'username' => '2026091',
            'email' => '2026091@santri.test',
        ]);
        $user->assignRole(Role::Santri);

        $santri = SantriProfile::query()->create([
            'user_id' => $user->id,
            'nis' => '2026091',
            'gender' => Gender::Perempuan,
            'track' => SantriTrack::Iqro,
            'iqro_level' => 1,
            'status' => SantriStatus::Aktif,
        ]);

        return compact('ketua', 'santri');
    }
}
