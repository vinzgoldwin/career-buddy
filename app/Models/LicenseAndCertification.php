<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseAndCertification extends Model
{
    /** @use HasFactory<\Database\Factories\LicenseAndCertificationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'issuing_organization',
        'description',
        'issue_date',
        'expiration_date',
        'credential_id',
        'credential_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'expiration_date' => 'date',
    ];

    /**
     * Get the user that owns the license or certification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
