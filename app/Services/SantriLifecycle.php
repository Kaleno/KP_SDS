<?php

namespace App\Services;

use App\Enums\SantriStatus;
use App\Models\SantriProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SantriLifecycle
{
    public function applyStatus(SantriProfile $santri, SantriStatus $status, ?SantriStatus $previous = null): void
    {
        $previous ??= $santri->status;
        $santri->status = $status;

        if ($status === SantriStatus::Lulus) {
            $santri->graduated_at ??= now()->toDateString();
        } else {
            $santri->graduated_at = null;
        }

        if ($previous !== SantriStatus::Aktif && $status === SantriStatus::Aktif) {
            $santri->spp_obligation_from = now()->startOfMonth()->toDateString();
        }

        if ($status === SantriStatus::Aktif && $santri->spp_obligation_from === null) {
            $santri->spp_obligation_from = ($santri->joined_at ?? now())->copy()->startOfMonth()->toDateString();
        }

        $santri->save();

        $santri->loadMissing('user');
        if ($santri->user) {
            $santri->user->update([
                'is_active' => $status->allowsLogin(),
            ]);
        }
    }

    public function syncLogin(User $user, SantriStatus $status): void
    {
        $user->update(['is_active' => $status->allowsLogin()]);
    }

    public function storePhoto(?UploadedFile $photo, ?string $existingPath = null): ?string
    {
        if ($photo === null) {
            return $existingPath;
        }

        if ($existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        return $photo->store('santri-photos', 'public');
    }

    /**
     * @return array{years: int, months: int, days: int, label: string}
     */
    public function membershipDuration(SantriProfile $santri): array
    {
        $from = ($santri->joined_at ?? $santri->created_at ?? now())->copy()->startOfDay();
        $to = ($santri->graduated_at ?? now())->copy()->startOfDay();

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        $interval = $from->diff($to);
        $years = (int) $interval->y;
        $months = (int) $interval->m;
        $days = (int) $interval->d;

        return [
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'label' => "{$years} tahun {$months} bulan {$days} hari",
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detailPayload(SantriProfile $santri): array
    {
        $santri->loadMissing('user');
        $duration = $this->membershipDuration($santri);

        return [
            'id' => $santri->id,
            'name' => $santri->user->name,
            'nis' => $santri->nis,
            'photo_url' => $santri->photoUrl(),
            'birth_date' => $santri->birth_date?->format('d-m-Y') ?? '—',
            'phone' => $santri->user->phone ?: '—',
            'address' => $santri->address ?: '—',
            'parent_name' => $santri->parent_name ?: '—',
            'track' => $santri->track?->label() ?? '—',
            'status' => $santri->status->label(),
            'joined_at' => $santri->joined_at?->format('d-m-Y') ?? '—',
            'duration' => $duration['label'],
            'graduated_at' => $santri->graduated_at?->format('d-m-Y') ?? '—',
        ];
    }
}
