<?php

namespace App\Models;

use App\Enums\EducationLevel;
use App\Support\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'place_name',
    'username',
    'nip',
    'email',
    'phone',
    'birth_date',
    'address',
    'education_level',
    'photo_path',
    'password',
    'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'birth_date' => 'date',
            'education_level' => EducationLevel::class,
        ];
    }

    /**
     * @return HasOne<SantriProfile, $this>
     */
    public function santriProfile(): HasOne
    {
        return $this->hasOne(SantriProfile::class);
    }

    /**
     * @return BelongsToMany<SantriProfile, $this>
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(SantriProfile::class, 'parent_student', 'parent_user_id', 'santri_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Halaqah, $this>
     */
    public function guidedHalaqah(): HasMany
    {
        return $this->hasMany(Halaqah::class, 'ustaz_user_id');
    }

    public function isKetua(): bool
    {
        return $this->hasRole(Role::Ketua);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }
}
