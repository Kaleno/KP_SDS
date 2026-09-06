<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'start_date', 'end_date', 'is_active'])]
class AcademicYear extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Halaqah, $this>
     */
    public function halaqah(): HasMany
    {
        return $this->hasMany(Halaqah::class);
    }

    /**
     * @param  Builder<AcademicYear>  $query
     * @return Builder<AcademicYear>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function markAsActive(): void
    {
        static::query()->where('id', '!=', $this->id)->update(['is_active' => false]);

        $this->update(['is_active' => true]);
    }
}
