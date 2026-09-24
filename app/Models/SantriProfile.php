<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Enums\SchoolLevel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'nis',
    'parent_name',
    'address',
    'school_level',
    'track',
    'iqro_level',
    'photo_path',
    'gender',
    'birth_date',
    'status',
    'joined_at',
    'graduated_at',
    'spp_obligation_from',
])]
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
            'school_level' => SchoolLevel::class,
            'track' => SantriTrack::class,
            'birth_date' => 'date',
            'joined_at' => 'date',
            'graduated_at' => 'date',
            'spp_obligation_from' => 'date',
            'iqro_level' => 'integer',
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

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }
}
