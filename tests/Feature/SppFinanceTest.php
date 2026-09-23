<?php

namespace Tests\Feature;

use App\Enums\FinanceType;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Models\FinanceEntry;
use App\Models\SantriProfile;
use App\Models\SppPayment;
use App\Models\User;
use App\Services\SppService;
use App\Support\AppSettings;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppFinanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_ketua_pengajar_records_spp_without_finance_entry_and_clears_tunggakan(): void
    {
        $leader = User::factory()->create();
        $leader->assignRole(Role::KetuaPengajar);
        $santri = $this->makeSantri('2026001', 'Ahmad');

        $this->actingAs($leader)->post(route('ops.spp.store'), [
            'santri_id' => $santri->id,
            'from_year' => now()->year,
            'from_month' => now()->month,
            'to_year' => now()->year,
            'to_month' => now()->month,
            'paid_at' => now()->toDateString(),
            'note' => 'Bayar tunai',
        ])->assertRedirect();

        $this->assertDatabaseHas('spp_payments', [
            'santri_id' => $santri->id,
            'year' => now()->year,
            'month' => now()->month,
            'amount' => app(SppService::class)->monthlyAmount(),
        ]);

        $this->assertDatabaseMissing('finance_entries', [
            'spp_payment_id' => SppPayment::query()->first()->id,
        ]);
        $this->assertSame(0, FinanceEntry::query()->where('source', 'spp')->count());

        $this->actingAs($leader)
            ->get(route('ops.spp.index'))
            ->assertOk()
            ->assertSee('Menunggak (0)')
            ->assertSee('Semua santri aktif sudah bayar');
    }

    public function test_multi_month_payment_creates_one_row_per_month_without_finance_entry(): void
    {
        $leader = User::factory()->create();
        $leader->assignRole(Role::KetuaPengajar);
        $santri = $this->makeSantri('2026010', 'Budi');
        $santri->forceFill(['created_at' => now()->subMonths(2)->startOfMonth()])->save();

        $from = now()->subMonths(2);
        $to = now()->subMonth();
        $months = 2;

        $this->actingAs($leader)->post(route('ops.spp.store'), [
            'santri_id' => $santri->id,
            'from_year' => $from->year,
            'from_month' => $from->month,
            'to_year' => $to->year,
            'to_month' => $to->month,
            'paid_at' => now()->toDateString(),
            'note' => 'Lunas 2 bulan',
        ])->assertRedirect();

        $this->assertSame($months, SppPayment::query()->where('santri_id', $santri->id)->count());
        $this->assertSame(0, FinanceEntry::query()->count());
        $this->assertSame(
            1,
            SppPayment::query()->where('santri_id', $santri->id)->distinct()->count('batch_id'),
        );

        $this->actingAs($leader)
            ->get(route('ops.spp.index'))
            ->assertOk()
            ->assertSee('Riwayat pembayaran')
            ->assertSee('Rp '.number_format($months * app(SppService::class)->monthlyAmount(), 0, ',', '.'));

        $otherMonth = now()->month === 1 ? 12 : now()->month - 1;
        $otherYear = now()->month === 1 ? now()->year - 1 : now()->year;

        $this->actingAs($leader)
            ->get(route('ops.spp.index', [
                'year' => $otherYear,
                'month' => $otherMonth,
            ]))
            ->assertOk()
            ->assertSee('Tidak ada pembayaran.');
    }

    public function test_obligation_starts_from_activation_month(): void
    {
        $santri = $this->makeSantri('2026011', 'Citra');
        $santri->forceFill(['created_at' => now()->subMonths(3)->startOfMonth()->addDays(20)])->save();

        $spp = app(SppService::class);
        $unpaid = $spp->unpaidPeriods($santri);

        $this->assertCount(4, $unpaid);
        $this->assertSame(
            now()->subMonths(3)->format('Y-m'),
            $unpaid[0]['key'],
        );
    }

    public function test_ketua_dashboard_shows_spp_summary(): void
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);
        $this->makeSantri('2026012', 'Dina');

        $this->actingAs($ketua)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan SPP')
            ->assertSee('Belum bayar bulan ini')
            ->assertSee('Nunggak');
    }

    public function test_ketua_can_add_manual_pengeluaran_and_santri_sees_payment_info(): void
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);
        $santriUser = User::factory()->create(['name' => 'Siti']);
        $santriUser->assignRole(Role::Santri);
        $santri = SantriProfile::query()->create([
            'user_id' => $santriUser->id,
            'nis' => '2026002',
            'gender' => Gender::Perempuan,
            'track' => SantriTrack::Iqro,
            'status' => SantriStatus::Aktif,
        ]);

        $this->actingAs($ketua)->post(route('ketua.finance.store'), [
            'type' => FinanceType::Pengeluaran->value,
            'amount' => 50000,
            'entry_date' => now()->toDateString(),
            'category' => 'Listrik',
            'note' => 'Tagihan bulan ini',
        ])->assertRedirect(route('ketua.finance.index'));

        $this->assertSame(1, FinanceEntry::query()->where('type', FinanceType::Pengeluaran)->count());

        $this->actingAs($santriUser)
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Info pembayaran')
            ->assertSee('Belum bayar');

        $leader = User::factory()->create();
        $leader->assignRole(Role::KetuaPengajar);
        app(SppService::class)->record($santri, $leader, [
            'year' => now()->year,
            'month' => now()->month,
            'paid_at' => now()->toDateString(),
        ]);

        $this->actingAs($santriUser)
            ->get(route('portal.home'))
            ->assertOk()
            ->assertSee('Lunas');
    }

    public function test_pengajar_cannot_open_spp_menu(): void
    {
        $pengajar = User::factory()->create();
        $pengajar->assignRole(Role::Pengajar);

        $this->actingAs($pengajar)->get(route('ops.spp.index'))->assertForbidden();
    }

    public function test_ketua_can_update_spp_settings_including_due_day(): void
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($ketua)
            ->put(route('ketua.finance.spp-amount'), [
                'spp_amount' => 30000,
                'spp_due_day' => 15,
            ])
            ->assertRedirect(route('ketua.finance.index'));

        $this->assertSame(30000, app(SppService::class)->monthlyAmount());
        $this->assertSame(15, AppSettings::sppDueDay());

        $leader = User::factory()->create();
        $leader->assignRole(Role::KetuaPengajar);
        $santri = $this->makeSantri('2026099', 'Siti');

        $this->actingAs($leader)->post(route('ops.spp.store'), [
            'santri_id' => $santri->id,
            'from_year' => now()->year,
            'from_month' => now()->month,
            'to_year' => now()->year,
            'to_month' => now()->month,
            'paid_at' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('spp_payments', [
            'santri_id' => $santri->id,
            'amount' => 30000,
        ]);
    }

    private function makeSantri(string $nis, string $name): SantriProfile
    {
        $user = User::factory()->create(['name' => $name, 'username' => $nis]);
        $user->assignRole(Role::Santri);

        return SantriProfile::query()->create([
            'user_id' => $user->id,
            'nis' => $nis,
            'gender' => Gender::LakiLaki,
            'track' => SantriTrack::Alquran,
            'status' => SantriStatus::Aktif,
        ]);
    }
}
