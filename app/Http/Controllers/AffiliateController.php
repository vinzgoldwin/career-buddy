<?php

namespace App\Http\Controllers;

use App\Models\AffiliateCode;
use App\Models\Referral;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AffiliateController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get or create affiliate code for the user
        $affiliateCode = AffiliateCode::firstOrCreate(
            ['user_id' => $user->id],
            [
                'code' => AffiliateCode::generateUniqueCode(),
                'is_active' => true,
            ]
        );

        // Get referral statistics
        $totalReferrals = Referral::where('referrer_id', $user->id)->count();
        $registeredReferrals = Referral::where('referrer_id', $user->id)
            ->where('status', 'registered')
            ->count();
        $transactedReferrals = Referral::where('referrer_id', $user->id)
            ->where('status', 'transacted')
            ->count();
        $totalEarnings = Referral::where('referrer_id', $user->id)
            ->sum('commission_earned');

        return Inertia::render('Affiliate', [
            'totalReferrals' => $totalReferrals,
            'registeredReferrals' => $registeredReferrals,
            'transactedReferrals' => $transactedReferrals,
            'totalEarnings' => $totalEarnings,
            'referralCode' => $affiliateCode->code,
            'referralLink' => $affiliateCode->referral_link,
        ]);
    }
}
