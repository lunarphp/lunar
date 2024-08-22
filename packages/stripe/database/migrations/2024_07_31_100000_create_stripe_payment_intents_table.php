<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'stripe_payment_intents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained($this->prefix.'carts');
            $table->foreignId('order_id')->nullable()->constrained($this->prefix.'orders');
            $table->string('intent_id')->index();
            $table->string('status')->nullable();
            $table->string('event_id')->index()->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'stripe_payment_intents');
    }
};
