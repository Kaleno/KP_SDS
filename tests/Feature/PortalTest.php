<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Enums\SchoolLevel;
use App\Enums\SetoranStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\Role;
use Database\Seeders\QuranSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->travelTo('2026-09-23 10:00:00');
    }

    public function test_santri_sees_same_progress_and_setoran_without_input_form(): void
    {
        $fx = $this->portalFixture();
        $this->seed(QuranSeeder::class);
        $this->storeFatihah($fx, $fx['santri'][0], SetoranStatus::Lulus);

        $this->actingAs($fx['santri'][0]->user)
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Al-Fatihah')
            ->assertSee('4.73%')
            ->assertSee('hanya melihat')
            ->assertDontSee('Input setoran')
            ->assertDontSee('Simpan setoran')
            ->assertDontSee('Jadwal halaqah')
            ->assertDontSee('Halaqah Tahfidz');
    }

    public function test_santri_cannot_open_ops(): void
    {
        $fx = $this->portalFixture();

        $this->actingAs($fx['santri'][0]->user)
            ->get(route('ops.setoran.create'))
            ->assertForbidden();

        $this->actingAs($fx['santri'][0]->user)
            ->post(route('ops.setoran.store'), [])
            ->assertForbidden();
    }

    public function test_portal_shows_attendance_without_kelas_jadwal_framing(): void
    {
        $fx = $this->portalFixture();
        $session = AttendanceSession::query()->create([
            'session_date' => now()->toDateString(),
            'opened_by_user_id' => $fx['ustaz']->id,
            'submitted_at' => now(),
        ]);
        Attendance::query()->create([
            'attendance_session_id' => $session->id,
            'santri_id' => $fx['santri'][0]->id,
            'status' => AttendanceStatus::Hadir,
        ]);

        $this->actingAs($fx['santri'][0]->user)
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Hadir')
            ->assertSee('Hari ini')
            ->assertDontSee('Jadwal halaqah')
            ->assertDontSee('07:00')
            ->assertDontSee('Halaqah Tahfidz');
    }

    public function test_portal_explains_ulang_setoran_in_plain_language(): void
    {
        $fx = $this->portalFixture();
        $this->seed(QuranSeeder::class);
        $this->storeFatihah($fx, $fx['santri'][0], SetoranStatus::Mengulang);

        $this->actingAs($fx['santri'][0]->user)
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Al-Fatihah')
            ->assertSee('Mengulang')
            ->assertSee('Perlu diulang')
            ->assertSee('pertemuan berikutnya')
            ->assertDontSee('Peta 30 juz')
            ->assertDontSee('Input setoran')
            ->assertDontSee('Simpan setoran');
    }

    public function test_portal_shows_current_profile_fields(): void
    {
        $fx = $this->portalFixture();
        $santri = $fx['santri'][0];
        $santri->user->update(['phone' => '081234567890']);
        $santri->update([
            'parent_name' => 'Bapak Fauzi',
            'address' => 'Jl. Masjid No. 1',
            'school_level' => SchoolLevel::Sd,
            'track' => SantriTrack::Iqro,
            'iqro_level' => 2,
            'birth_date' => '2012-01-15',
            'joined_at' => '2026-01-01',
            'status' => SantriStatus::Aktif,
        ]);

        $this->actingAs($santri->user)
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Data diri')
            ->assertSee('Aktif belajar')
            ->assertSee('Laki-laki')
            ->assertSee('15 Januari 2012')
            ->assertSee('SD')
            ->assertSee('Iqro')
            ->assertSee('Jilid 2')
            ->assertSee('Bapak Fauzi')
            ->assertSee('081234567890')
            ->assertSee('Jl. Masjid No. 1')
            ->assertSee('1 Januari 2026')
            ->assertSee('0 tahun 8 bulan 22 hari')
            ->assertDontSee('Jadwal');
    }

    public function test_graduated_santri_still_sees_graduation_date(): void
    {
        $fx = $this->portalFixture();
        $santri = $fx['santri'][0];
        $santri->update([
            'status' => SantriStatus::Lulus,
            'joined_at' => '2024-01-01',
            'graduated_at' => '2026-06-01',
        ]);

        $this->actingAs($santri->user)
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Lulus')
            ->assertSee('1 Juni 2026')
            ->assertSee('2 tahun 5 bulan 0 hari');
    }

    /**
     * @return array{ustaz: User, santri: list<SantriProfile>}
     */
    private function portalFixture(): array
    {
        $ustaz = $this->userWithRole(Role::KetuaPengajar, ['username' => 'ustaz1']);

        $santri = [];
        foreach (['2026001' => 'Ahmad Fauzi', '2026002' => 'Hasan Basri', '2026003' => 'Yusuf Maulana'] as $nis => $name) {
            $santri[] = $this->makeSantri($nis, $name);
        }

        return compact('ustaz', 'santri');
    }

    /**
     * @param  array{ustaz: User}  $fx
     */
    private function storeFatihah(array $fx, SantriProfile $santri, SetoranStatus $status): void
    {
        HafalanSetoran::query()->create([
            'santri_id' => $santri->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
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
