<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\RegistrationStatus;
use App\Enums\SantriTrack;
use App\Enums\SchoolLevel;
use App\Models\SantriProfile;
use App\Models\SantriRegistration;
use App\Models\User;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SantriRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_can_submit_registration(): void
    {
        Storage::fake('public');

        $this->post(route('daftar.store'), [
            'name' => 'Siti Aminah',
            'parent_name' => 'Bapak Amin',
            'school_level' => SchoolLevel::Sd->value,
            'birth_date' => '2015-03-01',
            'gender' => Gender::Perempuan->value,
            'track' => SantriTrack::Iqro->value,
            'photo' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertRedirect(route('daftar.create'));

        $this->assertDatabaseHas('santri_registrations', [
            'name' => 'Siti Aminah',
            'status' => RegistrationStatus::Pending->value,
            'track' => SantriTrack::Iqro->value,
        ]);
    }

    public function test_ketua_can_approve_registration_with_credentials(): void
    {
        Storage::fake('public');
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        $registration = SantriRegistration::query()->create([
            'name' => 'Siti Aminah',
            'parent_name' => 'Bapak Amin',
            'school_level' => SchoolLevel::Sd,
            'birth_date' => '2015-03-01',
            'gender' => Gender::Perempuan,
            'track' => SantriTrack::Iqro,
            'status' => RegistrationStatus::Pending,
        ]);

        $this->actingAs($ketua)->post(route('ketua.registrations.approve', $registration), [
            'username' => 'siti1',
            'nis' => '2026999',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('ketua.registrations.index'));

        $this->assertTrue($registration->fresh()->status === RegistrationStatus::Approved);
        $santri = SantriProfile::query()->where('nis', '2026999')->first();
        $this->assertNotNull($santri);
        $this->assertTrue($santri->user->hasRole(Role::Santri));
        $this->assertSame(SantriTrack::Iqro, $santri->track);
        $this->assertSame(1, $santri->iqro_level);
    }

    public function test_ketua_can_reject_registration(): void
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        $registration = SantriRegistration::query()->create([
            'name' => 'Tolak Saya',
            'parent_name' => 'Ortu',
            'school_level' => SchoolLevel::Tk,
            'birth_date' => '2018-01-01',
            'gender' => Gender::LakiLaki,
            'track' => SantriTrack::Alquran,
            'status' => RegistrationStatus::Pending,
        ]);

        $this->actingAs($ketua)->post(route('ketua.registrations.reject', $registration), [
            'rejection_note' => 'Data kurang lengkap',
        ])->assertRedirect(route('ketua.registrations.index'));

        $this->assertSame(RegistrationStatus::Rejected, $registration->fresh()->status);
    }

    public function test_santri_can_update_own_profile(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'Ahmad']);
        $user->assignRole(Role::Santri);
        $santri = SantriProfile::query()->create([
            'user_id' => $user->id,
            'nis' => '2026001',
            'gender' => Gender::LakiLaki,
            'track' => SantriTrack::Alquran,
            'status' => 'aktif',
        ]);

        $this->actingAs($user)->put(route('portal.profile.update'), [
            'name' => 'Ahmad Updated',
            'parent_name' => 'Bapak Baru',
            'school_level' => SchoolLevel::Smp->value,
            'birth_date' => '2012-05-05',
            'photo' => UploadedFile::fake()->image('baru.jpg'),
        ])->assertRedirect(route('portal.profile.edit'));

        $santri->refresh();
        $this->assertSame('Ahmad Updated', $santri->user->fresh()->name);
        $this->assertSame('Bapak Baru', $santri->parent_name);
        $this->assertSame(SchoolLevel::Smp, $santri->school_level);
        $this->assertNotNull($santri->photo_path);
    }
}
