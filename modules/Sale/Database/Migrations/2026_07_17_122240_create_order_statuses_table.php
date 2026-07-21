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
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            
            $table->string('status'); // pending, processing, etc.
            $table->string('notes')->nullable();
            
            // Who changed the status?
            $table->boolean('is_system')->default(false); // true if system auto-changed
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
