<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'discount_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained($this->prefix.'discounts')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained($this->prefix.'orders')->nullOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained($this->prefix.'carts')->nullOnDelete();
            $table->userForeignKey(nullable: true);
            $table->string('email')->nullable();
            $table->timestamp('redeemed_at')->index();

            $table->unique(['discount_id', 'order_id']);
            $table->index(['discount_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'discount_redemptions');
    }
};
