<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_autofill_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('job_title')->nullable();
            $table->string('company')->nullable();
            $table->string('source_host')->nullable();
            $table->text('page_url')->nullable();
            $table->json('fields')->nullable();
            $table->unsignedInteger('filled_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_autofill_events');
    }
};
