<?php

namespace App\Support;

use App\Models\SantriProfile;
use App\Models\User;

class PortalAccess
{
    public function canMonitor(User $user, SantriProfile $santri): bool
    {
        if ($user->hasRole(Role::Santri)) {
            return (int) $user->santriProfile?->id === (int) $santri->id;
        }

        if ($user->hasRole(Role::OrangTua)) {
            return $user->children()->where('santri_profiles.id', $santri->id)->exists();
        }

        return false;
    }

    public function assertMonitor(User $user, SantriProfile $santri): void
    {
        abort_unless($this->canMonitor($user, $santri), 403);
    }
}
