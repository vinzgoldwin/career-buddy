<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'location',
        'website',
        'summary',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the experiences for the user.
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /**
     * Get the education records for the user.
     */
    public function education(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    /**
     * Get the skills for the user.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * Get the projects for the user.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the licenses and certifications for the user.
     */
    public function licensesAndCertifications(): HasMany
    {
        return $this->hasMany(LicenseAndCertification::class);
    }

    /**
     * Get the user's balance.
     */
    public function balance(): HasOne
    {
        return $this->hasOne(UserBalance::class);
    }

    /**
     * Get the user's transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the user's affiliate code.
     */
    public function affiliateCode(): HasOne
    {
        return $this->hasOne(AffiliateCode::class);
    }

    /**
     * Get referrals made by this user.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Get the referral that brought this user.
     */
    public function referredBy(): HasOne
    {
        return $this->hasOne(Referral::class, 'referred_user_id');
    }
}
