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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            // Order number for customer reference (unique, human-readable)
            $table->string('order_number', 20)->unique();
            
            // Order status (using string for flexibility)
            $table->string('status', 30)->default('pending'); // pending, processing, shipped, delivered, cancelled, refunded
            
            // Customer information snapshot
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            
            // Shipping address
            $table->string('shipping_address_line1');
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_state')->nullable();
            $table->string('shipping_postal_code');
            $table->string('shipping_country', 2);
            
            // Billing address (could be same as shipping)
            $table->string('billing_address_line1');
            $table->string('billing_address_line2')->nullable();
            $table->string('billing_city');
            $table->string('billing_state')->nullable();
            $table->string('billing_postal_code');
            $table->string('billing_country', 2);
            
            // Financials
            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2);
            $table->decimal('shipping_cost', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            
            // Shipping method
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            
            // Payment tracking
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();

            // User relationship
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            
            // Indexes for fast lookups
            $table->index('order_number');
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
