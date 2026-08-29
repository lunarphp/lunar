<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'paypal_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->nullable()->constrained($this->prefix.'carts');
            $table->foreignId('order_id')->nullable()->constrained($this->prefix.'orders');
            $table->string('paypal_order_id')->unique();
            $table->string('status')->nullable();
            $table->string('event_id')->nullable()->index();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'paypal_orders');
    }
};
