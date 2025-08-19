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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']);
            $table->unsignedInteger('amount'); // Amount in the smallest currency unit (cents)
            $table->string('description');
            $table->enum('status', ['completed', 'pending', 'failed'])->default('pending');
            $table->string('reference_id')->nullable(); // For external payment references
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
