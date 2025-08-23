<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_evaluation_changes', function (Blueprint $table) {
            $table->timestamp('applied_at')->nullable()->after('new_value');
        });
    }

    public function down(): void
    {
        Schema::table('profile_evaluation_changes', function (Blueprint $table) {
            $table->dropColumn('applied_at');
        });
    }
};
