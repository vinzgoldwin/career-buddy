<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'affiliate_code',
        'registered_at',
        'first_transaction_at',
        'commission_earned',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'first_transaction_at' => 'datetime',
            'commission_earned' => 'integer',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function getFormattedCommissionAttribute(): string
    {
        return number_format($this->commission_earned, 0, ',', '.');
    }
}
