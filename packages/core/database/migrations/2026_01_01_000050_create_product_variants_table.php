<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'product_variants', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('product_id')->constrained($this->prefix.'products');
            // Merchant availability toggle: a disabled variant is never
            // purchasable, regardless of product status or stock.
            $table->boolean('enabled')->default(true)->index();
            $table->foreignId('tax_class_id')->constrained($this->prefix.'tax_classes');
            $table->string('tax_ref')->index()->nullable();
            $table->integer('unit_quantity')->unsigned()->index()->default(1);
            $table->string('sku')->nullable()->index();
            $table->string('gtin')->nullable()->index();
            $table->string('mpn')->nullable()->index();
            $table->string('ean')->nullable()->index();
            $table->dimensions();
            $table->boolean('shippable')->default(true)->index();
            $table->integer('backorder')->default(0)->index();
            $table->string('selling_policy')->default('always')->index();
            // Cached stock rollup, summed from the variant's stock levels (and the
            // global committed/reserved counters). `stock_available` is the indexed
            // sellable figure: on_hand - committed - reserved - unavailable.
            $table->integer('stock_on_hand')->default(0);
            $table->integer('stock_incoming')->default(0);
            $table->integer('stock_committed')->default(0);
            $table->integer('stock_reserved')->default(0);
            $table->integer('stock_unavailable')->default(0);
            $table->integer('stock_available')->default(0)->index();
            $table->integer('quantity_increment')->unsigned()->default(1)->index();
            $table->integer('min_quantity')->unsigned()->default(1)->index();
            $table->jsonb('attribute_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'product_variants');
    }
};
