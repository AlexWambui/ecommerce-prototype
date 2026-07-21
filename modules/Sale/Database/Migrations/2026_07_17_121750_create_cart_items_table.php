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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            
            // Product snapshot (to preserve price even if product changes)
            $table->string('product_name');
            $table->string('product_sku');
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity')->default(1);
            
            // Variant tracking (if you have variants)
            $table->json('attributes')->nullable(); // e.g., {"size": "M", "color": "Red"}
            
            // Line total calculation
            $table->decimal('subtotal', 12, 2); // quantity * unit_price
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2); // subtotal + tax - discount

            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            // Product relationship (from your Product module)
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            $table->timestamps();
            
            // Ensure unique product per cart (can't add same product twice)
            $table->unique(['cart_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
