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
        Schema::table('profile_evaluations', function (Blueprint $table) {
            $table->dropColumn(['overall_recommendation', 'improvements']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_evaluations', function (Blueprint $table) {
            $table->text('overall_recommendation')->nullable();
            $table->text('improvements')->nullable();
        });
    }
};
