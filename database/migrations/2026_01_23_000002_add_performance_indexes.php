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
        Schema::table('courses', function (Blueprint $table) {
            $table->index('status');
            $table->index('is_paid');
            $table->index(['status', 'is_paid']); // Composite for common queries
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->index('revoked_at');
            $table->index(['course_id', 'revoked_at']); // For access checks
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('status');
            $table->index(['user_id', 'status']); // For user payment history
        });

        Schema::table('lesson_progress', function (Blueprint $table) {
            $table->index('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['is_paid']);
            $table->dropIndex(['status', 'is_paid']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['revoked_at']);
            $table->dropIndex(['course_id', 'revoked_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('lesson_progress', function (Blueprint $table) {
            $table->dropIndex(['is_completed']);
        });
    }
};
