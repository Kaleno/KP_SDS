<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['academic_year_id', 'ustaz_user_id', 'name', 'is_active'])]
class Halaqah extends Model
{
    protected $table = 'halaqah';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function ustaz(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ustaz_user_id');
    }

    /**
     * @return HasMany<HalaqahMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(HalaqahMember::class);
    }

    /**
     * @return HasMany<HalaqahMember, $this>
     */
    public function activeMembers(): HasMany
    {
        return $this->members()->whereNull('ended_at');
    }

    /**
     * @return HasMany<Schedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * @return HasMany<HafalanSetoran, $this>
     */
    public function setoran(): HasMany
    {
        return $this->hasMany(HafalanSetoran::class);
    }

    /**
     * @param  Builder<Halaqah>  $query
     * @return Builder<Halaqah>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
