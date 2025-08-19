<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBalance extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 0, ',', '.');
    }
}
