<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'cart_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ulid('public_id')->unique();
            $table->foreignId('cart_id')->constrained($this->prefix.'carts');
            $table->morphs('purchasable');
            $table->unsignedInteger('quantity');
            $table->jsonb('meta')->nullable();
            $table->timestamps();

            // Persisted totals snapshot (spec 0076). NULL means "not calculated".
            $table->unsignedBigInteger('unit_price')->nullable();
            $table->unsignedBigInteger('unit_price_incl_tax')->nullable();
            $table->unsignedBigInteger('sub_total')->nullable();
            $table->unsignedBigInteger('sub_total_discounted')->nullable();
            $table->unsignedBigInteger('discount_total')->nullable();
            $table->unsignedBigInteger('tax_total')->nullable();
            $table->unsignedBigInteger('total')->nullable();
            $table->jsonb('tax_breakdown')->nullable();
            $table->string('promotion_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'cart_lines');
    }
};
