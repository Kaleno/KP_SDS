<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\SantriProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class OperationalAccess
{
    public function canOperateDaily(User $user): bool
    {
        return $user->hasRole(Role::Ketua) || $user->hasRole(Role::Ustaz);
    }

    public function canOperateHalaqah(User $user, Halaqah $halaqah): bool
    {
        if ($user->hasRole(Role::Ketua)) {
            return true;
        }

        return $user->hasRole(Role::Ustaz) && (int) $halaqah->ustaz_user_id === (int) $user->id;
    }

    public function canViewSantri(User $user, SantriProfile $santri): bool
    {
        if ($user->hasRole(Role::Ketua)) {
            return true;
        }

        return $this->guidedMemberQuery($user)
            ->where('santri_id', $santri->id)
            ->exists();
    }

    /**
     * @return Builder<Halaqah>
     */
    public function halaqahQuery(User $user): Builder
    {
        $query = Halaqah::query()->where('academic_year_id', $this->activeYearId());

        if ($user->hasRole(Role::Ketua)) {
            return $query;
        }

        return $query->where('ustaz_user_id', $user->id);
    }

    /**
     * @return Builder<HalaqahMember>
     */
    public function guidedMemberQuery(User $user): Builder
    {
        $yearId = $this->activeYearId();

        return HalaqahMember::query()
            ->aktif()
            ->where('academic_year_id', $yearId)
            ->whereHas('halaqah', function (Builder $query) use ($user, $yearId): void {
                $query->aktif()->where('academic_year_id', $yearId);

                if (! $user->hasRole(Role::Ketua)) {
                    $query->where('ustaz_user_id', $user->id);
                }
            });
    }

    private function activeYearId(): int
    {
        return (int) (AcademicYear::query()->aktif()->value('id') ?: 0);
    }

    /**
     * @return list<int>|null null = semua santri (Ketua)
     */
    public function santriIds(User $user): ?array
    {
        if ($user->hasRole(Role::Ketua)) {
            return null;
        }

        return $this->guidedMemberQuery($user)->pluck('santri_id')->all();
    }

    public function assertHalaqah(User $user, Halaqah $halaqah): void
    {
        abort_unless($this->canOperateHalaqah($user, $halaqah), 403);
    }

    public function assertSantri(User $user, SantriProfile $santri): void
    {
        abort_unless($this->canViewSantri($user, $santri), 403);
    }
}
