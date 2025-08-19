<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users or create a test user if none exist
        $users = User::all();
        
        if ($users->isEmpty()) {
            $users = collect([
                User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ])
            ]);
        }

        foreach ($users as $user) {
            // Create user balance if it doesn't exist
            $balance = UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            // Create sample transactions
            $transactions = [
                [
                    'type' => 'credit',
                    'amount' => 200000, // Rp 200,000
                    'description' => 'Welcome Bonus',
                    'status' => 'completed',
                    'created_at' => now()->subDays(10),
                ],
                [
                    'type' => 'debit',
                    'amount' => 10000, // Rp 10,000
                    'description' => 'Auto Apply Credits',
                    'status' => 'completed',
                    'created_at' => now()->subDays(9),
                ],
                [
                    'type' => 'credit',
                    'amount' => 100000, // Rp 100,000
                    'description' => 'Top up via Bank Transfer',
                    'status' => 'completed',
                    'created_at' => now()->subDays(7),
                ],
                [
                    'type' => 'debit',
                    'amount' => 15000, // Rp 15,000
                    'description' => 'Mock Interview Session',
                    'status' => 'completed',
                    'created_at' => now()->subDays(5),
                ],
                [
                    'type' => 'debit',
                    'amount' => 25000, // Rp 25,000
                    'description' => 'AI Resume Builder Usage',
                    'status' => 'completed',
                    'created_at' => now()->subDays(3),
                ],
                [
                    'type' => 'credit',
                    'amount' => 50000, // Rp 50,000
                    'description' => 'Top up via E-Wallet',
                    'status' => 'pending',
                    'created_at' => now()->subHours(2),
                ],
            ];

            foreach ($transactions as $transactionData) {
                Transaction::create([
                    'user_id' => $user->id,
                    ...$transactionData
                ]);
            }

            // Calculate final balance
            $totalCredits = Transaction::where('user_id', $user->id)
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->sum('amount');

            $totalDebits = Transaction::where('user_id', $user->id)
                ->where('type', 'debit')
                ->where('status', 'completed')
                ->sum('amount');

            $finalBalance = $totalCredits - $totalDebits;

            // Update user balance
            $balance->update(['balance' => $finalBalance]);
        }
    }
}
