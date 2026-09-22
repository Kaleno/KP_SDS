<?php

namespace App\Models;

use App\Enums\FinanceSource;
use App\Enums\FinanceType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'type',
    'source',
    'amount',
    'entry_date',
    'category',
    'note',
    'spp_payment_id',
    'created_by',
])]
class FinanceEntry extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => FinanceType::class,
            'source' => FinanceSource::class,
            'amount' => 'integer',
            'entry_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<SppPayment, $this>
     */
    public function sppPayment(): BelongsTo
    {
        return $this->belongsTo(SppPayment::class);
    }
}
