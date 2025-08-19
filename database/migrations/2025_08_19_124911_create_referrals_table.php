<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_user_id')->constrained('users')->onDelete('cascade');
            $table->string('affiliate_code', 10);
            $table->timestamp('registered_at');
            $table->timestamp('first_transaction_at')->nullable();
            $table->unsignedInteger('commission_earned')->default(0); // in cents
            $table->enum('status', ['registered', 'transacted'])->default('registered');
            $table->timestamps();

            $table->index(['referrer_id', 'status']);
            $table->index('affiliate_code');
            $table->unique(['referrer_id', 'referred_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
