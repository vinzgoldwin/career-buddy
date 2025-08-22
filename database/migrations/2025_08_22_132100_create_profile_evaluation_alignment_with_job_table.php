<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_evaluation_alignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_evaluation_id')->constrained()->cascadeOnDelete();
            $table->integer('skills_match_score')->nullable();
            $table->text('skills_match_feedback')->nullable();
            $table->integer('job_title_match_score')->nullable();
            $table->text('job_title_match_feedback')->nullable();
            $table->integer('responsibilities_qualifications_score')->nullable();
            $table->text('responsibilities_qualifications_feedback')->nullable();
            $table->integer('industry_keywords_synonyms_score')->nullable();
            $table->text('industry_keywords_synonyms_feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_evaluation_alignments');
    }
};