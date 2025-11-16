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
            $table->foreignId('booking_id')->constrained('bookings')->restrictOnDelete();
            $table->enum('provider', ['stripe', 'razorpay', 'paypal', 'upi']);
            $table->string('provider_payment_id');
            $table->enum('status', ['pending', 'authorized', 'captured', 'failed', 'refunded'])->default('pending');
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('INR');
            $table->string('idempotency_key')->unique();
            $table->json('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('provider_payment_id');
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
