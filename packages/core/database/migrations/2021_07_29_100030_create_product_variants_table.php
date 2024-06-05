<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained($this->prefix.'products');
            $table->foreignId('tax_class_id')->constrained($this->prefix.'tax_classes');
            $table->string('tax_ref')->index()->nullable();
            $table->integer('unit_quantity')->unsigned()->index()->default(1);
            $table->string('sku')->nullable()->index();
            $table->string('gtin')->nullable()->index();
            $table->string('mpn')->nullable()->index();
            $table->string('ean')->nullable()->index();
            $table->dimensions();
            $table->boolean('shippable')->default(true)->index();
            $table->integer('stock')->default(0)->index();
            $table->integer('backorder')->default(0)->index();
            $table->string('purchasable')->default('always')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'product_variants');
    }
};
