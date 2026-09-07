<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\Location;
use App\Models\SantriProfile;
use App\Models\Schedule;
use App\Models\User;
use App\Support\Role;

class HalaqahReadiness
{
    /**
     * @return array{ready: bool, checks: list<array{key: string, label: string, done: bool}>}
     */
    public function snapshot(): array
    {
        $year = AcademicYear::query()->aktif()->first();
        $checks = [
            ['key' => 'year', 'label' => 'Tahun ajaran aktif', 'done' => $year !== null],
            ['key' => 'location', 'label' => 'Lokasi pertemuan', 'done' => Location::query()->exists()],
            ['key' => 'ustaz', 'label' => 'Akun pengajar', 'done' => User::query()->role(Role::Ustaz)->where('is_active', true)->exists()],
            ['key' => 'santri', 'label' => 'Santri aktif', 'done' => SantriProfile::query()->aktif()->exists()],
            ['key' => 'halaqah', 'label' => 'Halaqah aktif', 'done' => $year !== null && Halaqah::query()->aktif()->where('academic_year_id', $year->id)->exists()],
            ['key' => 'members', 'label' => 'Anggota halaqah', 'done' => $year !== null && HalaqahMember::query()->aktif()->where('academic_year_id', $year->id)->exists()],
            ['key' => 'schedule', 'label' => 'Jadwal mingguan', 'done' => $year !== null && Schedule::query()
                ->where('is_active', true)
                ->whereHas('halaqah', fn ($query) => $query->aktif()->where('academic_year_id', $year->id))
                ->exists()],
        ];

        return [
            'ready' => collect($checks)->every(fn (array $check): bool => $check['done']),
            'checks' => $checks,
        ];
    }
}
