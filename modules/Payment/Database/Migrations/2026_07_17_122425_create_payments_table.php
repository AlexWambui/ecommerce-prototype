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
            
            // Payment provider details
            $table->string('provider', 50); // stripe, paypal, mpesa, etc.
            $table->string('transaction_id')->unique(); // External payment ID
            
            // Payment details
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status', 30)->default('pending'); // pending, completed, failed, refunded
            
            // Payment method details
            $table->string('payment_method')->nullable(); // credit_card, mpesa, paypal
            $table->string('card_last_four')->nullable();
            $table->string('card_brand')->nullable(); // visa, mastercard
            
            // M-Pesa specific
            $table->string('mpesa_receipt')->nullable();
            $table->string('mpesa_phone')->nullable();
            
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
            $table->index('transaction_id');
            $table->index('status');
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
