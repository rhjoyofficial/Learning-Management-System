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
        Schema::table('payments', function (Blueprint $table) {
            // Add coupon_id foreign key
            $table->foreignId('coupon_id')
                ->nullable()
                ->after('course_id')
                ->constrained()
                ->nullOnDelete();

            // Add currency field
            if (!Schema::hasColumn('payments', 'currency')) {
                $table->string('currency', 3)
                    ->default('BDT')
                    ->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'currency']);
        });
    }
};
