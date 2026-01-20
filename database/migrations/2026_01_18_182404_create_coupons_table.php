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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->enum('discount_type', ['percentage', 'fixed', 'free']);
            $table->decimal('discount_value', 10, 2)->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('course_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
            $table->index(['code', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
