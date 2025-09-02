<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAutofillEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'resume_variant',
        'job_title',
        'company',
        'source_host',
        'page_url',
        'fields',
        'field_details',
        'filled_count',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'field_details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
