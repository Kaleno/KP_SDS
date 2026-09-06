<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['halaqah_id', 'santri_id', 'academic_year_id', 'started_at', 'ended_at', 'mutation_note', 'active_slot'])]
class HalaqahMember extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
            'active_slot' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (HalaqahMember $member): void {
            $member->active_slot = $member->ended_at ? null : 1;

            if (! $member->academic_year_id && $member->halaqah_id) {
                $member->academic_year_id = $member->halaqah?->academic_year_id
                    ?? Halaqah::query()->whereKey($member->halaqah_id)->value('academic_year_id');
            }
        });
    }

    /**
     * @return BelongsTo<Halaqah, $this>
     */
    public function halaqah(): BelongsTo
    {
        return $this->belongsTo(Halaqah::class);
    }

    /**
     * @return BelongsTo<SantriProfile, $this>
     */
    public function santri(): BelongsTo
    {
        return $this->belongsTo(SantriProfile::class, 'santri_id');
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @param  Builder<HalaqahMember>  $query
     * @return Builder<HalaqahMember>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereNull('ended_at');
    }
}
