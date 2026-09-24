<?php

namespace Tests\Feature;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Enums\SetoranCategory;
use App\Enums\SetoranStatus;
use App\Enums\SetoranSubtype;
use App\Models\HafalanSetoran;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UstazManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_ketua_can_create_ustadz_with_profile_fields(): void
    {
        Storage::fake('public');
        $ketua = $this->ketua();

        $this->actingAs($ketua)->post(route('ketua.ustaz.store'), [
            'teaching_role' => Role::Pengajar,
            'name' => 'Ustadz Ahmad',
            'nip' => 'NIP-001',
            'username' => 'ahmad1',
            'phone' => '08111',
            'birth_date' => '1990-01-15',
            'address' => 'Jl. Masjid 1',
            'education_level' => EducationLevel::S1->value,
            'is_active' => '1',
            'password' => 'password',
            'password_confirmation' => 'password',
            'photo' => UploadedFile::fake()->image('ustadz.jpg'),
        ])->assertRedirect(route('ketua.ustaz.index'));

        $ustaz = User::query()->where('username', 'ahmad1')->first();
        $this->assertNotNull($ustaz);
        $this->assertSame('NIP-001', $ustaz->nip);
        $this->assertSame('Jl. Masjid 1', $ustaz->address);
        $this->assertSame(EducationLevel::S1, $ustaz->education_level);
        $this->assertNotNull($ustaz->photo_path);
        $this->assertTrue($ustaz->hasRole(Role::Pengajar));
    }

    public function test_nip_must_be_unique(): void
    {
        $ketua = $this->ketua();
        $this->makeUstadz('NIP-100', 'lama');

        $this->actingAs($ketua)->post(route('ketua.ustaz.store'), [
            'teaching_role' => Role::Pengajar,
            'name' => 'Duplikat',
            'nip' => 'NIP-100',
            'username' => 'baru1',
            'is_active' => '1',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('nip');
    }

    public function test_index_shows_actions_and_ustadz_copy(): void
    {
        $ketua = $this->ketua();
        $this->makeUstadz('NIP-200', 'ustadz2');

        $this->actingAs($ketua)
            ->get(route('ketua.ustaz.index'))
            ->assertOk()
            ->assertSee('Pengajar')
            ->assertSee('Detail Ustadz', false)
            ->assertSee('title="Lihat"', false)
            ->assertSee('title="Ubah"', false)
            ->assertSee('title="Reset password"', false)
            ->assertSee('title="Hapus"', false)
            ->assertSee('Nonaktifkan');
    }

    public function test_ketua_can_reset_ustadz_password_to_default(): void
    {
        $ketua = $this->ketua();
        $ustaz = $this->makeUstadz('NIP-250', 'ustadz25');
        $ustaz->update(['password' => 'changed-secret']);

        $this->assertTrue(Hash::check('changed-secret', $ustaz->fresh()->password));

        $this->actingAs($ketua)
            ->patch(route('ketua.ustaz.reset-password', $ustaz))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('password', $ustaz->fresh()->password));
    }

    public function test_soft_delete_hides_from_list_but_keeps_setoran_history(): void
    {
        $ketua = $this->ketua();
        $ustaz = $this->makeUstadz('NIP-300', 'ustadz3');
        $santri = $this->makeSantri();

        HafalanSetoran::query()->create([
            'santri_id' => $santri->id,
            'ustaz_user_id' => $ustaz->id,
            'category' => SetoranCategory::Bacaan,
            'subtype' => SetoranSubtype::Iqro,
            'iqro_level' => 1,
            'iqro_page' => 1,
            'setoran_date' => now()->toDateString(),
            'status' => SetoranStatus::Lulus,
        ]);

        $this->actingAs($ketua)
            ->delete(route('ketua.ustaz.destroy', $ustaz))
            ->assertRedirect(route('ketua.ustaz.index'));

        $this->assertSoftDeleted('users', ['id' => $ustaz->id]);
        $this->assertDatabaseHas('hafalan_setoran', ['ustaz_user_id' => $ustaz->id]);

        $this->actingAs($ketua)
            ->get(route('ketua.ustaz.index'))
            ->assertOk()
            ->assertDontSee('NIP-300');

        $setoran = HafalanSetoran::query()->first();
        $this->assertSame($ustaz->id, $setoran->ustaz()->first()->id);
        $this->assertSame('Ustadz demo', $setoran->ustaz->name);
    }

    public function test_toggle_activates_and_deactivates(): void
    {
        $ketua = $this->ketua();
        $ustaz = $this->makeUstadz('NIP-400', 'ustadz4');

        $this->actingAs($ketua)
            ->patch(route('ketua.ustaz.toggle', $ustaz))
            ->assertRedirect();

        $this->assertFalse($ustaz->fresh()->is_active);

        $this->actingAs($ketua)
            ->patch(route('ketua.ustaz.toggle', $ustaz))
            ->assertRedirect();

        $this->assertTrue($ustaz->fresh()->is_active);
    }

    private function ketua(): User
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        return $ketua;
    }

    private function makeUstadz(string $nip, string $username): User
    {
        $ustaz = User::factory()->create([
            'name' => 'Ustadz demo',
            'username' => $username,
            'nip' => $nip,
            'is_active' => true,
        ]);
        $ustaz->assignRole(Role::Pengajar);

        return $ustaz;
    }

    private function makeSantri(): SantriProfile
    {
        $user = User::factory()->create(['username' => '2026999']);
        $user->assignRole(Role::Santri);

        return SantriProfile::query()->create([
            'user_id' => $user->id,
            'nis' => '2026999',
            'gender' => Gender::LakiLaki,
            'track' => SantriTrack::Alquran,
            'status' => SantriStatus::Aktif,
            'joined_at' => now()->toDateString(),
            'spp_obligation_from' => now()->startOfMonth()->toDateString(),
        ]);
    }
}
