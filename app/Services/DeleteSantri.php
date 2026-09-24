<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\HafalanSetoran;
use App\Models\SantriProfile;
use App\Models\SppPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteSantri
{
    public function handle(SantriProfile $santri): void
    {
        DB::transaction(function () use ($santri): void {
            $santri->loadMissing('user');

            Attendance::query()->where('santri_id', $santri->id)->delete();
            HafalanSetoran::query()->where('santri_id', $santri->id)->delete();
            SppPayment::query()->where('santri_id', $santri->id)->delete();

            if ($santri->photo_path) {
                Storage::disk('public')->delete($santri->photo_path);
            }

            $user = $santri->user;
            $santri->delete();

            if ($user) {
                $user->roles()->detach();
                $user->forceDelete();
            }
        });
    }
}
