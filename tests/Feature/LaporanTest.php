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
use Database\Seeders\QuranSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->travelTo('2026-09-23 10:00:00');
    }

    public function test_ketua_dashboard_shows_summary_without_daily_ops_queue(): void
    {
        $fx = $this->opsFixture();

        $this->actingAs($fx['ketua'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Santri aktif')
            ->assertSee('Ringkasan SPP')
            ->assertSee('Minggu ini')
            ->assertSee('Keuangan')
            ->assertDontSee('Absensi hari ini')
            ->assertDontSee('Setoran hari ini')
            ->assertDontSee('Alfa hari ini')
            ->assertDontSee('Kelas aktif')
            ->assertDontSee('Jadwal hari ini');
    }

    public function test_setoran_history_counts_mengulang_without_hiding_other_rows(): void
    {
        $fx = $this->opsFixture();
        $this->seedFatihah();

        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri'][0]->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lulus,
        ]);
        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri'][1]->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Mengulang,
        ]);

        $this->actingAs($fx['ustaz'])
            ->get(route('ops.setoran.index'))
            ->assertOk()
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Hasan Basri')
            ->assertViewHas('summary', [
                'active' => 3,
                'hafalan' => ['sudah' => 0, 'belum' => 3, 'ulang' => 0],
                'bacaan' => ['sudah' => 2, 'belum' => 1, 'ulang' => 1],
            ]);
    }

    public function test_attendance_recap_counts_alfa_per_santri_without_halaqah_filter(): void
    {
        $fx = $this->opsFixture();
        $this->openMixedAttendance($fx);

        $this->actingAs($fx['ketua'])
            ->get(route('laporan.attendance.index', [
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Yusuf Maulana')
            ->assertSee('Rekap')
            ->assertDontSee('Semua halaqah')
            ->assertDontSee('name="halaqah_id"', false);
    }

    public function test_any_pengajar_can_open_progress_of_active_santri(): void
    {
        $fx = $this->opsFixture();
        $this->seed(QuranSeeder::class);
        $outsider = $this->userWithRole(Role::Pengajar, ['username' => 'ustaz2']);

        $this->actingAs($outsider)
            ->get(route('laporan.progress.bacaan.show', $fx['santri'][0]))
            ->assertOk()
            ->assertSee('Ahmad Fauzi');

        $this->actingAs($fx['ustaz'])
            ->get(route('laporan.progress.bacaan.show', $fx['santri'][0]))
            ->assertOk()
            ->assertSee('Ahmad Fauzi');
    }

    public function test_progress_bacaan_shows_active_juz_after_fatihah_lancar(): void
    {
        $fx = $this->opsFixture();
        $this->seed(QuranSeeder::class);

        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri'][0]->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'activity_type' => 'ngaji',
            'category' => 'bacaan',
            'subtype' => 'alquran',
            'quran_surah_id' => 1,
            'setoran_date' => now()->toDateString(),
            'ayah_start' => 1,
            'ayah_end' => 7,
            'status' => SetoranStatus::Lulus,
        ]);

        $this->actingAs($fx['ketua'])
            ->get(route('laporan.progress.bacaan.show', $fx['santri'][0]))
            ->assertOk()
            ->assertSee('4.73%')
            ->assertSee('Al-Fatihah')
            ->assertSee('Juz 1');

        $this->actingAs($fx['ketua'])
            ->get(route('laporan.progress.bacaan.index'))
            ->assertOk()
            ->assertSee('4.73%')
            ->assertSee('Juz 1 dikerjakan');
    }

    public function test_progress_hafalan_shows_doa_and_juz30(): void
    {
        $fx = $this->opsFixture();
        $this->seed(QuranSeeder::class);

        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri'][0]->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'activity_type' => 'hafalan',
            'category' => 'hafalan',
            'subtype' => 'doa',
            'doa_name' => 'Doa sebelum makan',
            'setoran_date' => now()->toDateString(),
            'status' => SetoranStatus::Mengulang,
        ]);

        HafalanSetoran::query()->create([
            'santri_id' => $fx['santri'][0]->id,
            'ustaz_user_id' => $fx['ustaz']->id,
            'activity_type' => 'hafalan',
            'category' => 'hafalan',
            'subtype' => 'juz30',
            'quran_surah_id' => 78,
            'ayah_start' => 1,
            'ayah_end' => 5,
            'setoran_date' => now()->toDateString(),
            'status' => SetoranStatus::Lulus,
        ]);

        $this->actingAs($fx['ketua'])
            ->get(route('laporan.progress.hafalan.show', $fx['santri'][0]))
            ->assertOk()
            ->assertSee('Doa sebelum makan')
            ->assertSee('Mengulang')
            ->assertSee('An-Naba')
            ->assertSee('Juz 30');
    }

    /**
     * @return array{ketua: User, ustaz: User, santri: list<SantriProfile>}
     */
    private function opsFixture(): array
    {
        $ketua = $this->userWithRole(Role::Ketua);
        $ustaz = $this->userWithRole(Role::KetuaPengajar, ['username' => 'ustaz1']);

        $santri = [];
        foreach (['2026001' => 'Ahmad Fauzi', '2026002' => 'Hasan Basri', '2026003' => 'Yusuf Maulana'] as $nis => $name) {
            $santri[] = $this->makeSantri($nis, $name);
        }

        return compact('ketua', 'ustaz', 'santri');
    }

    /**
     * @param  array{ustaz: User, santri: list<SantriProfile>}  $fx
     */
    private function openMixedAttendance(array $fx): AttendanceSession
    {
        $session = AttendanceSession::query()->create([
            'session_date' => now()->toDateString(),
            'opened_by_user_id' => $fx['ustaz']->id,
        ]);

        $statuses = [AttendanceStatus::Hadir, AttendanceStatus::Izin, AttendanceStatus::Alfa];
        foreach ($fx['santri'] as $index => $santri) {
            Attendance::query()->create([
                'attendance_session_id' => $session->id,
                'santri_id' => $santri->id,
                'status' => $statuses[$index],
            ]);
        }

        return $session;
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
