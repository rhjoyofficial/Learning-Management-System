<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_progress', function (Blueprint $table) {
            // Add unique constraint for user_id + course_id
            $table->unique(['user_id', 'course_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            // Add unique constraint for user_id + course_id
            // (User can only review a course once)
            $table->unique(['user_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_progress', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'course_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'course_id']);
        });
    }
};
