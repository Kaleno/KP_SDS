<?php

namespace App\Services;

use App\Enums\RegistrationStatus;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Models\SantriProfile;
use App\Models\SantriRegistration;
use App\Models\User;
use App\Support\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApproveSantriRegistration
{
    /**
     * @param  array{username: string, password: string, nis: string}  $credentials
     */
    public function handle(SantriRegistration $registration, User $reviewer, array $credentials): SantriProfile
    {
        abort_unless($registration->status === RegistrationStatus::Pending, 422);

        return DB::transaction(function () use ($registration, $reviewer, $credentials) {
            $photoPath = $registration->photo_path;
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                $target = 'santri-photos/'.basename($photoPath);
                Storage::disk('public')->move($photoPath, $target);
                $photoPath = $target;
            }

            $user = User::query()->create([
                'name' => $registration->name,
                'username' => $credentials['username'],
                'password' => $credentials['password'],
                'is_active' => true,
            ]);
            $user->assignRole(Role::Santri);

            $santri = SantriProfile::query()->create([
                'user_id' => $user->id,
                'nis' => $credentials['nis'],
                'parent_name' => $registration->parent_name,
                'school_level' => $registration->school_level,
                'track' => $registration->track,
                'iqro_level' => $registration->track === SantriTrack::Iqro ? 1 : null,
                'photo_path' => $photoPath,
                'gender' => $registration->gender,
                'birth_date' => $registration->birth_date,
                'status' => SantriStatus::Aktif,
                'joined_at' => now()->toDateString(),
                'spp_obligation_from' => now()->startOfMonth()->toDateString(),
            ]);

            $registration->update([
                'status' => RegistrationStatus::Approved,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'santri_id' => $santri->id,
                'photo_path' => $photoPath,
            ]);

            return $santri;
        });
    }

    public function reject(SantriRegistration $registration, User $reviewer, ?string $note = null): void
    {
        abort_unless($registration->status === RegistrationStatus::Pending, 422);

        $registration->update([
            'status' => RegistrationStatus::Rejected,
            'rejection_note' => $note,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }
}
