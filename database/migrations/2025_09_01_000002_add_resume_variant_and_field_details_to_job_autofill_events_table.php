<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_autofill_events', function (Blueprint $table) {
            $table->string('resume_variant')->nullable()->after('user_id');
            $table->json('field_details')->nullable()->after('fields');
        });
    }

    public function down(): void
    {
        Schema::table('job_autofill_events', function (Blueprint $table) {
            $table->dropColumn(['resume_variant', 'field_details']);
        });
    }
};
