<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_answer_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_question_id')->constrained('interview_questions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('answer');
            $table->unsignedTinyInteger('overall_performance_score')->nullable();
            $table->unsignedTinyInteger('structural_integrity_score')->nullable();
            $table->unsignedTinyInteger('content_accuracy_score')->nullable();
            $table->unsignedTinyInteger('fluency_of_expression_score')->nullable();
            $table->json('strengths')->nullable();
            $table->json('priority_areas_for_improvement')->nullable();
            $table->json('comparative_analysis')->nullable();
            $table->json('encouraging_advice')->nullable();
            $table->longText('raw_output')->nullable();
            $table->text('errors')->nullable();
            $table->json('usage')->nullable();
            $table->string('llm_model')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_answer_evaluations');
    }
};

