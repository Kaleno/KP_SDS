<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\User;
use App\Support\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_ketua_can_manage_holidays(): void
    {
        $ketua = User::factory()->create();
        $ketua->assignRole(Role::Ketua);

        $this->actingAs($ketua)
            ->get(route('ketua.holidays.index'))
            ->assertOk()
            ->assertSee('Tanggal merah');

        $this->actingAs($ketua)
            ->post(route('ketua.holidays.store'), [
                'date' => '2026-10-01',
                'name' => 'Libur khusus',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertTrue(
            Holiday::query()->whereDate('date', '2026-10-01')->where('name', 'Libur khusus')->exists()
        );
    }

    public function test_ketua_pengajar_can_manage_holidays(): void
    {
        $leader = User::factory()->create();
        $leader->assignRole(Role::KetuaPengajar);

        $this->actingAs($leader)
            ->get(route('ketua.holidays.index'))
            ->assertOk()
            ->assertSee('Libur');

        $this->actingAs($leader)
            ->post(route('ketua.holidays.store'), [
                'date' => '2026-10-02',
                'name' => 'Libur pengajar',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $holiday = Holiday::query()->whereDate('date', '2026-10-02')->first();
        $this->assertNotNull($holiday);

        $this->actingAs($leader)
            ->delete(route('ketua.holidays.destroy', $holiday))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }

    public function test_pengajar_cannot_manage_holidays(): void
    {
        $ustaz = User::factory()->create();
        $ustaz->assignRole(Role::Pengajar);

        $this->actingAs($ustaz)
            ->get(route('ketua.holidays.index'))
            ->assertForbidden();

        $this->actingAs($ustaz)
            ->post(route('ketua.holidays.store'), [
                'date' => '2026-10-03',
                'name' => 'Tidak boleh',
            ])
            ->assertForbidden();
    }
}
