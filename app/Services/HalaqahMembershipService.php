<?php

namespace App\Services;

use App\Enums\SantriStatus;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\SantriProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HalaqahMembershipService
{
    public function add(Halaqah $halaqah, SantriProfile $santri, string $startedAt): HalaqahMember
    {
        $this->assertEligible($halaqah, $santri);

        return HalaqahMember::query()->create([
            'halaqah_id' => $halaqah->id,
            'santri_id' => $santri->id,
            'academic_year_id' => $halaqah->academic_year_id,
            'started_at' => $startedAt,
        ]);
    }

    public function mutate(HalaqahMember $member, Halaqah $target, string $movedAt, ?string $note): HalaqahMember
    {
        if ($member->ended_at) {
            throw ValidationException::withMessages([
                'target_halaqah_id' => 'Keanggotaan ini sudah tidak aktif.',
            ]);
        }

        if ($target->id === $member->halaqah_id) {
            throw ValidationException::withMessages([
                'target_halaqah_id' => 'Pilih halaqah tujuan yang berbeda.',
            ]);
        }

        if ((int) $target->academic_year_id !== (int) $member->academic_year_id) {
            throw ValidationException::withMessages([
                'target_halaqah_id' => 'Mutasi hanya dalam tahun ajaran yang sama.',
            ]);
        }

        return DB::transaction(function () use ($member, $target, $movedAt, $note) {
            $member->update([
                'ended_at' => $movedAt,
                'mutation_note' => $note,
            ]);

            return $this->add($target, $member->santri, $movedAt);
        });
    }

    public function end(HalaqahMember $member, string $endedAt, ?string $note = null): void
    {
        if ($member->ended_at) {
            throw ValidationException::withMessages([
                'member' => 'Keanggotaan ini sudah ditutup.',
            ]);
        }

        $member->update([
            'ended_at' => $endedAt,
            'mutation_note' => $note,
        ]);
    }

    private function assertEligible(Halaqah $halaqah, SantriProfile $santri): void
    {
        if ($santri->status !== SantriStatus::Aktif) {
            throw ValidationException::withMessages([
                'santri_id' => 'Hanya santri berstatus aktif yang bisa masuk halaqah.',
            ]);
        }

        $alreadyIn = HalaqahMember::query()
            ->where('santri_id', $santri->id)
            ->where('academic_year_id', $halaqah->academic_year_id)
            ->whereNull('ended_at')
            ->exists();

        if ($alreadyIn) {
            throw ValidationException::withMessages([
                'santri_id' => 'Santri sudah berada di halaqah aktif pada tahun ajaran ini.',
            ]);
        }
    }
}
