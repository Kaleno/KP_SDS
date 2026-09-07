<?php

namespace App\Services;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Models\AcademicYear;
use App\Models\Halaqah;
use App\Models\Location;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrepareHalaqah
{
    public function __construct(
        private HalaqahMembershipService $memberships,
        private ScheduleConflictChecker $conflicts,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Halaqah
    {
        return DB::transaction(function () use ($data): Halaqah {
            $year = $this->resolveYear($data);
            $location = $this->resolveLocation($data);
            $ustaz = $this->resolveUstaz($data);
            $santri = $this->resolveSantri($data);

            $halaqah = Halaqah::query()->create([
                'name' => $data['halaqah_name'],
                'academic_year_id' => $year->id,
                'ustaz_user_id' => $ustaz->id,
                'is_active' => true,
            ]);

            $start = $this->withSeconds((string) $data['start_time']);
            $end = $this->withSeconds((string) $data['end_time']);

            if ($this->conflicts->ustazOverlaps($halaqah, (int) $data['day_of_week'], $start, $end)) {
                throw ValidationException::withMessages([
                    'start_time' => 'Jam ini bentrok dengan jadwal ustaz yang sama.',
                ]);
            }

            $halaqah->schedules()->create([
                'location_id' => $location->id,
                'day_of_week' => (int) $data['day_of_week'],
                'start_time' => $start,
                'end_time' => $end,
                'is_active' => true,
            ]);

            $this->memberships->add($halaqah, $santri, $year->start_date->toDateString());

            return $halaqah->load(['ustaz', 'schedules.location', 'activeMembers.santri.user']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveYear(array $data): AcademicYear
    {
        if (($data['year_source'] ?? 'new') === 'existing') {
            $year = AcademicYear::query()->findOrFail($data['academic_year_id']);
            if (! $year->is_active) {
                $year->markAsActive();
            }

            return $year;
        }

        $year = AcademicYear::query()->create([
            'name' => $data['year_name'],
            'start_date' => $data['year_start_date'],
            'end_date' => $data['year_end_date'],
            'is_active' => false,
        ]);
        $year->markAsActive();

        return $year;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveLocation(array $data): Location
    {
        if (($data['location_source'] ?? 'new') === 'existing') {
            return Location::query()->findOrFail($data['location_id']);
        }

        return Location::query()->create([
            'name' => $data['location_name'],
            'description' => $data['location_description'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveUstaz(array $data): User
    {
        if (($data['ustaz_source'] ?? 'new') === 'existing') {
            $ustaz = User::query()->findOrFail($data['ustaz_user_id']);
            abort_unless($ustaz->hasRole(Role::Ustaz), 422);

            return $ustaz;
        }

        $ustaz = User::query()->create([
            'name' => $data['ustaz_name'],
            'username' => $data['ustaz_username'],
            'email' => $data['ustaz_email'] ?? null,
            'password' => $data['ustaz_password'],
            'is_active' => true,
        ]);
        $ustaz->assignRole(Role::Ustaz);

        return $ustaz;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveSantri(array $data): SantriProfile
    {
        if (($data['santri_source'] ?? 'new') === 'existing') {
            return SantriProfile::query()->findOrFail($data['santri_id']);
        }

        $user = User::query()->create([
            'name' => $data['santri_name'],
            'username' => $data['santri_nis'],
            'email' => $data['santri_email'] ?? null,
            'password' => $data['santri_password'],
            'is_active' => true,
        ]);
        $user->assignRole(Role::Santri);

        return SantriProfile::query()->create([
            'user_id' => $user->id,
            'nis' => $data['santri_nis'],
            'gender' => $data['santri_gender'] ?? Gender::LakiLaki->value,
            'status' => SantriStatus::Aktif,
        ]);
    }

    private function withSeconds(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }
}
