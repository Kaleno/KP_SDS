<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Models\SantriProfile;
use App\Models\User;
use App\Services\SppService;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SantriManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_ketua_can_create_santri_with_address_photo_and_joined_at(): void
    {
        Storage::fake('public');
        $ketua = $this->ketua();

        $this->actingAs($ketua)->post(route('ketua.santri.store'), [
            'name' => 'Aida Salsabila',
            'nis' => '2026101',
            'gender' => Gender::Perempuan->value,
            'birth_date' => '2012-05-15',
            'parent_name' => 'Ortu Aida',
            'address' => 'Jl. Puspitek, Pamulang',
            'track' => SantriTrack::Alquran->value,
            'status' => SantriStatus::Aktif->value,
            'phone' => '08123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
            'photo' => UploadedFile::fake()->image('aida.jpg'),
        ])->assertRedirect(route('ketua.santri.index'));

        $santri = SantriProfile::query()->where('nis', '2026101')->first();
        $this->assertNotNull($santri);
        $this->assertSame('Jl. Puspitek, Pamulang', $santri->address);
        $this->assertNotNull($santri->photo_path);
        $this->assertNotNull($santri->joined_at);
        $this->assertNotNull($santri->spp_obligation_from);
        $this->assertTrue($santri->user->is_active);
        $this->assertNull($santri->graduated_at);
    }

    public function test_index_shows_detail_actions_and_status_labels(): void
    {
        $ketua = $this->ketua();
        $this->makeSantri('2026102', 'Budi', SantriStatus::Aktif);

        $this->actingAs($ketua)
            ->get(route('ketua.santri.index'))
            ->assertOk()
            ->assertSee('Aktif belajar')
            ->assertSee('Detail Santri', false)
            ->assertSee('title="Lihat"', false)
            ->assertSee('title="Ubah"', false)
            ->assertSee('title="Reset password"', false)
            ->assertSee('title="Hapus"', false);
    }

    public function test_ketua_can_reset_santri_password_to_default(): void
    {
        $ketua = $this->ketua();
        $santri = $this->makeSantri('2026109', 'Dewi', SantriStatus::Aktif);
        $santri->user->update(['password' => 'changed-secret']);

        $this->assertTrue(Hash::check('changed-secret', $santri->user->fresh()->password));

        $this->actingAs($ketua)
            ->patch(route('ketua.santri.reset-password', $santri))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('password', $santri->user->fresh()->password));
    }

    public function test_cuti_and_keluar_disable_login_but_lulus_keeps_login(): void
    {
        $ketua = $this->ketua();
        $santri = $this->makeSantri('2026103', 'Citra', SantriStatus::Aktif);

        $this->actingAs($ketua)->put(route('ketua.santri.update', $santri), $this->updatePayload($santri, [
            'status' => SantriStatus::Cuti->value,
        ]))->assertRedirect(route('ketua.santri.index'));

        $this->assertFalse($santri->fresh()->user->is_active);

        $this->actingAs($ketua)->put(route('ketua.santri.update', $santri), $this->updatePayload($santri, [
            'status' => SantriStatus::Lulus->value,
        ]))->assertRedirect(route('ketua.santri.index'));

        $fresh = $santri->fresh();
        $this->assertTrue($fresh->user->is_active);
        $this->assertNotNull($fresh->graduated_at);

        $this->actingAs($ketua)->put(route('ketua.santri.update', $santri), $this->updatePayload($santri, [
            'status' => SantriStatus::Keluar->value,
        ]))->assertRedirect(route('ketua.santri.index'));

        $this->assertFalse($santri->fresh()->user->is_active);
        $this->assertNull($santri->fresh()->graduated_at);
    }

    public function test_resume_from_cuti_resets_spp_obligation_to_current_month(): void
    {
        $ketua = $this->ketua();
        $santri = $this->makeSantri('2026104', 'Dina', SantriStatus::Aktif);
        $santri->forceFill([
            'joined_at' => now()->subMonths(4)->toDateString(),
            'spp_obligation_from' => now()->subMonths(4)->startOfMonth()->toDateString(),
            'created_at' => now()->subMonths(4),
        ])->save();

        $this->actingAs($ketua)->put(route('ketua.santri.update', $santri), $this->updatePayload($santri, [
            'status' => SantriStatus::Cuti->value,
        ]));

        $this->actingAs($ketua)->put(route('ketua.santri.update', $santri), $this->updatePayload($santri, [
            'status' => SantriStatus::Aktif->value,
        ]));

        $santri->refresh();
        $this->assertSame(
            now()->startOfMonth()->toDateString(),
            $santri->spp_obligation_from?->toDateString(),
        );

        $unpaid = app(SppService::class)->unpaidPeriods($santri);
        $this->assertCount(1, $unpaid);
        $this->assertSame(now()->format('Y-m'), $unpaid[0]['key']);
    }

    public function test_ketua_can_hard_delete_santri_and_user(): void
    {
        $ketua = $this->ketua();
        $santri = $this->makeSantri('2026105', 'Eko', SantriStatus::Aktif);
        $userId = $santri->user_id;

        $this->actingAs($ketua)
            ->delete(route('ketua.santri.destroy', $santri))
            ->assertRedirect(route('ketua.santri.index'));

        $this->assertDatabaseMissing('santri_profiles', ['id' => $santri->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    private function ketua(): User
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        return $ketua;
    }

    private function makeSantri(string $nis, string $name, SantriStatus $status): SantriProfile
    {
        $user = User::factory()->create([
            'name' => $name,
            'username' => $nis,
            'is_active' => $status->allowsLogin(),
        ]);
        $user->assignRole(Role::Santri);

        return SantriProfile::query()->create([
            'user_id' => $user->id,
            'nis' => $nis,
            'gender' => Gender::LakiLaki,
            'track' => SantriTrack::Alquran,
            'status' => $status,
            'parent_name' => 'Ortu',
            'address' => 'Alamat demo',
            'joined_at' => now()->toDateString(),
            'spp_obligation_from' => now()->startOfMonth()->toDateString(),
            'graduated_at' => $status === SantriStatus::Lulus ? now()->toDateString() : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function updatePayload(SantriProfile $santri, array $overrides = []): array
    {
        $santri->loadMissing('user');

        return array_merge([
            'name' => $santri->user->name,
            'nis' => $santri->nis,
            'gender' => $santri->gender->value,
            'birth_date' => $santri->birth_date?->format('Y-m-d'),
            'parent_name' => $santri->parent_name,
            'address' => $santri->address,
            'track' => $santri->track->value,
            'status' => $santri->status->value,
            'phone' => $santri->user->phone,
            'email' => $santri->user->email,
        ], $overrides);
    }
}
