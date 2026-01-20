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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();

            // Payment info
            $table->uuid('transaction_id')->unique(); // internal UUID
            $table->string('gateway'); // bkash, nagad, stripe, etc
            $table->string('gateway_payment_id')->nullable(); // bKash paymentID

            $table->decimal('amount', 10, 2);

            // Status
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])
                ->default('pending');

            // Refund info
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
