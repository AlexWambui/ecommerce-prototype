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
            $table->uuid()->unique();
            $table->string('payment_method'); // stripe, paypal, mpesa, etc.
            $table->string('transaction_reference')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('payment_status', 30)->default('pending'); // pending, completed, failed, refunded
            
            // Card specific
            $table->string('card_last_four')->nullable();
            $table->string('card_brand')->nullable(); // visa, mastercard
            
            // M-Pesa specific
            $table->string('mpesa_receipt')->nullable();
            $table->string('mpesa_phone')->nullable();
            $table->string('checkout_request_id')->nullable();
            $table->string('merchant_request_id')->nullable();
            $table->string('response_code')->nullable();
            
            // Failure tracking
            $table->text('failure_reason')->nullable();
            
            // Refund tracking
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refunded_amount', 12, 2)->default(0);
            
            // Full API response (for debugging)
            $table->json('provider_response')->nullable();

            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('transaction_reference');
            $table->index('payment_status');
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
