<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_evaluation_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_evaluation_id')->constrained()->cascadeOnDelete();
            $table->integer('problem_solving_score')->nullable();
            $table->text('problem_solving_feedback')->nullable();
            $table->integer('communication_collaboration_score')->nullable();
            $table->text('communication_collaboration_feedback')->nullable();
            $table->integer('initiative_innovation_score')->nullable();
            $table->text('initiative_innovation_feedback')->nullable();
            $table->integer('leadership_teamwork_score')->nullable();
            $table->text('leadership_teamwork_feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_evaluation_skills');
    }
};