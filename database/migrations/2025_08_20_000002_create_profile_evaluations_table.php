<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('job_description_id')->nullable()->constrained('job_descriptions')->cascadeOnDelete();
            $table->integer('total_score')->nullable();
            $table->json('impact')->nullable();
            $table->json('skills_and_traits')->nullable();
            $table->json('alignment_with_job')->nullable();
            $table->text('overall_recommendation')->nullable();
            $table->text('improvements')->nullable();
            $table->json('specific_changes')->nullable();
            $table->longText('raw_output')->nullable();
            $table->json('errors')->nullable();
            $table->json('usage')->nullable();
            $table->string('llm_model')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_evaluations');
    }
};
