<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\RegistrationStatus;
use App\Enums\SantriTrack;
use App\Enums\SchoolLevel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'parent_name',
    'school_level',
    'birth_date',
    'gender',
    'track',
    'photo_path',
    'status',
    'rejection_note',
    'reviewed_by',
    'reviewed_at',
    'santri_id',
])]
class SantriRegistration extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'school_level' => SchoolLevel::class,
            'gender' => Gender::class,
            'track' => SantriTrack::class,
            'status' => RegistrationStatus::class,
            'birth_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @return BelongsTo<SantriProfile, $this>
     */
    public function santri(): BelongsTo
    {
        return $this->belongsTo(SantriProfile::class, 'santri_id');
    }

    /**
     * @param  Builder<SantriRegistration>  $query
     * @return Builder<SantriRegistration>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::Pending);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }
}
