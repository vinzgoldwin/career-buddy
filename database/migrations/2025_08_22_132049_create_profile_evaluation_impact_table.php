<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_evaluation_impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_evaluation_id')->constrained()->cascadeOnDelete();
            $table->integer('quantifying_impact_score')->nullable();
            $table->text('quantifying_impact_feedback')->nullable();
            $table->integer('focus_on_achievements_score')->nullable();
            $table->text('focus_on_achievements_feedback')->nullable();
            $table->integer('writing_quality_score')->nullable();
            $table->text('writing_quality_feedback')->nullable();
            $table->integer('varied_industry_specific_verbs_score')->nullable();
            $table->text('varied_industry_specific_verbs_feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_evaluation_impacts');
    }
};