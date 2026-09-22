<?php

namespace App\Services;

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
        $hasSchedule = Schedule::query()
            ->where('is_active', true)
            ->whereHas('halaqah', fn ($query) => $query->aktif())
            ->exists();

        $checks = [
            ['key' => 'ustaz', 'label' => 'Akun pengajar', 'done' => User::query()->role(Role::teaching())->where('is_active', true)->exists()],
            ['key' => 'santri', 'label' => 'Santri aktif', 'done' => SantriProfile::query()->aktif()->exists()],
            ['key' => 'kelas', 'label' => 'Kelas dengan jadwal', 'done' => $hasSchedule],
        ];

        return [
            'ready' => collect($checks)->every(fn (array $check): bool => $check['done']),
            'checks' => $checks,
        ];
    }
}
