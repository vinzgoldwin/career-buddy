<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'difficulty',
        'views_count',
        'users_practiced_count',
        'explanation',
    ];

    protected $appends = [
        'time_ago',
    ];

    public function casts(): array
    {
        return [
            'views_count' => 'integer',
            'users_practiced_count' => 'integer',
            'explanation' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getTimeAgoAttribute(): ?string
    {
        return $this->created_at?->diffForHumans();
    }
}
