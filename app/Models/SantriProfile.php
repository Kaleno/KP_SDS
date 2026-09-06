<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'nis', 'gender', 'birth_date', 'status'])]
class SantriProfile extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'status' => SantriStatus::class,
            'birth_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'santri_id', 'parent_user_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany<HalaqahMember, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(HalaqahMember::class, 'santri_id');
    }

    /**
     * @return HasMany<HafalanSetoran, $this>
     */
    public function setoran(): HasMany
    {
        return $this->hasMany(HafalanSetoran::class, 'santri_id');
    }

    /**
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'santri_id');
    }

    /**
     * @param  Builder<SantriProfile>  $query
     * @return Builder<SantriProfile>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', SantriStatus::Aktif);
    }
}
