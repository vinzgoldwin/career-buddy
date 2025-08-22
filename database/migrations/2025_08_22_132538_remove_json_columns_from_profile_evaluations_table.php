<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'impact',
                'skills_and_traits',
                'alignment_with_job',
                'specific_changes'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('profile_evaluations', function (Blueprint $table) {
            $table->json('impact')->nullable();
            $table->json('skills_and_traits')->nullable();
            $table->json('alignment_with_job')->nullable();
            $table->json('specific_changes')->nullable();
        });
    }
};