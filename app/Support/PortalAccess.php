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

        return false;
    }

    public function assertMonitor(User $user, SantriProfile $santri): void
    {
        abort_unless($this->canMonitor($user, $santri), 403);
    }
}
