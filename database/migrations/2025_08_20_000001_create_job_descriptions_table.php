<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            // Core normalized fields (aligns with JobDescriptionParserService default schema)
            $table->string('title')->nullable();
            $table->string('seniority')->nullable();
            $table->string('company_name')->nullable();
            $table->string('work_mode')->nullable(); // Remote | Hybrid | Onsite
            $table->string('location')->nullable();
            $table->string('employment_type')->nullable();
            $table->text('summary')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('requirements')->nullable();
            $table->json('skills')->nullable();
            $table->integer('years_experience_min')->nullable();
            $table->integer('years_experience_max')->nullable();

            // Raw and meta
            $table->longText('raw_input'); // original pasted JD
            $table->longText('llm_output_raw')->nullable(); // raw LLM JSON/text
            $table->json('errors')->nullable();
            $table->json('usage')->nullable();
            $table->string('llm_model')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_descriptions');
    }
};
