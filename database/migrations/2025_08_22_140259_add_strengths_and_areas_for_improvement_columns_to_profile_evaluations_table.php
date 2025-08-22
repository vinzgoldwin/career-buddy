<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_evaluations', function (Blueprint $table) {
            $table->text('strengths')->nullable()->after('improvements');
            $table->text('areas_for_improvement')->nullable()->after('strengths');
        });
    }

    public function down(): void
    {
        Schema::table('profile_evaluations', function (Blueprint $table) {
            $table->dropColumn(['strengths', 'areas_for_improvement']);
        });
    }
};