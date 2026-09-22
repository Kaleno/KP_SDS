<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'santri_id',
    'year',
    'month',
    'amount',
    'paid_at',
    'recorded_by',
    'note',
])]
class SppPayment extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'amount' => 'integer',
            'paid_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<SantriProfile, $this>
     */
    public function santri(): BelongsTo
    {
        return $this->belongsTo(SantriProfile::class, 'santri_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * @return HasOne<FinanceEntry, $this>
     */
    public function financeEntry(): HasOne
    {
        return $this->hasOne(FinanceEntry::class);
    }

    public function periodLabel(): string
    {
        return sprintf('%02d/%d', $this->month, $this->year);
    }
}
