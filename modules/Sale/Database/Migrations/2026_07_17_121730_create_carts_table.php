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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            // Session identifier for guest carts
            $table->string('session_token', 100)->index();
            
            // Email capture for ghost accounts
            $table->string('email')->nullable()->index();
            
            // Cart metadata
            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('shipping', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            
            // Coupon/discount tracking
            $table->string('coupon_code')->nullable();
            
            // Expiry tracking
            $table->timestamp('expires_at')->nullable();

            // User association (nullable for guests)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            
            // Timestamps
            $table->timestamps();
            
            // Composite index for fast lookups
            $table->index(['session_token', 'user_id']);
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
