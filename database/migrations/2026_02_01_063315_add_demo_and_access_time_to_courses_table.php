<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('demo_video_url')->nullable()->after('thumbnail');

            $table->timestamp('start_at')->nullable()->after('status');
            $table->timestamp('end_at')->nullable()->after('start_at');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['demo_video_url', 'start_at', 'end_at']);
        });
    }
};
