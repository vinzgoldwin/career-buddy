<?php

namespace Database\Seeders;

use App\Models\AffiliateCode;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AffiliateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        if ($users->isEmpty()) {
            return;
        }

        // Create affiliate codes for existing users
        foreach ($users as $user) {
            AffiliateCode::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'code' => AffiliateCode::generateUniqueCode(),
                    'is_active' => true,
                ]
            );
        }

        // Create some sample referrals for demonstration
        if ($users->count() >= 2) {
            $mainUser = $users->first();
            $affiliateCode = $mainUser->affiliateCode;
            
            if ($affiliateCode) {
                // Create some sample referrals
                $sampleReferrals = [
                    [
                        'referred_user_id' => $users->skip(1)->first()->id ?? $users->first()->id,
                        'status' => 'transacted',
                        'registered_at' => now()->subDays(15),
                        'first_transaction_at' => now()->subDays(14),
                        'commission_earned' => 10000, // Rp 10,000
                    ],
                ];

                foreach ($sampleReferrals as $referralData) {
                    // Only create if the referred user is different from referrer
                    if ($referralData['referred_user_id'] !== $mainUser->id) {
                        Referral::firstOrCreate([
                            'referrer_id' => $mainUser->id,
                            'referred_user_id' => $referralData['referred_user_id'],
                        ], [
                            'affiliate_code' => $affiliateCode->code,
                            'registered_at' => $referralData['registered_at'],
                            'first_transaction_at' => $referralData['first_transaction_at'],
                            'commission_earned' => $referralData['commission_earned'],
                            'status' => $referralData['status'],
                        ]);
                    }
                }
            }
        }
    }
}
