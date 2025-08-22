<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_evaluation_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_evaluation_id')->constrained()->cascadeOnDelete();
            $table->string('field')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('specific_field')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_evaluation_changes');
    }
};