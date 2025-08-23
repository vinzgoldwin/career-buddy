<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileEvaluationSpecificChange extends Model
{
    use HasFactory;

    protected $table = 'profile_evaluation_changes';

    protected $fillable = [
        'profile_evaluation_id',
        'field',
        'entity_id',
        'specific_field',
        'old_value',
        'new_value',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
        ];
    }

    public function profileEvaluation(): BelongsTo
    {
        return $this->belongsTo(ProfileEvaluation::class);
    }
}
