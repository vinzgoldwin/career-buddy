<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_questions', function (Blueprint $table) {
            $table->text('explanation')->nullable()->after('users_practiced_count');
        });
    }

    public function down(): void
    {
        Schema::table('interview_questions', function (Blueprint $table) {
            $table->dropColumn('explanation');
        });
    }
};
