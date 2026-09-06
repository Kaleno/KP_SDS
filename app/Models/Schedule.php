<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['halaqah_id', 'location_id', 'day_of_week', 'start_time', 'end_time', 'is_active'])]
class Schedule extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Halaqah, $this>
     */
    public function halaqah(): BelongsTo
    {
        return $this->belongsTo(Halaqah::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return HasMany<AttendanceSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function timeRange(): string
    {
        return substr((string) $this->start_time, 0, 5).'–'.substr((string) $this->end_time, 0, 5);
    }
}
