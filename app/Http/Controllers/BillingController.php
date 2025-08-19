<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\UserBalance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get or create user balance
        $userBalance = UserBalance::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // Get user transactions (latest first)
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50) // Limit to recent 50 transactions
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'date' => $transaction->created_at->toISOString(),
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'status' => $transaction->status,
                ];
            });

        return Inertia::render('Billing', [
            'currentBalance' => $userBalance->balance,
            'transactions' => $transactions,
        ]);
    }
}
