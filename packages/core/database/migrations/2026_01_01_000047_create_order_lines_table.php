<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'order_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('order_id')->constrained($this->prefix.'orders');
            $table->morphs('purchasable');
            $table->string('type')->index();
            // Whether the line needs a fulfilment — stamped from the
            // purchasable's isShippable(); `type` is open-set display only.
            $table->boolean('requires_shipping')->default(false)->index();
            $table->string('description');
            $table->string('option')->nullable();
            $table->string('identifier')->index();
            $table->unsignedBigInteger('unit_price');
            $table->smallInteger('unit_quantity')->default(1)->unsigned()->index();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('sub_total');
            $table->unsignedBigInteger('discount_total');
            $table->jsonb('tax_breakdown');
            $table->unsignedBigInteger('tax_total');
            $table->unsignedBigInteger('total');
            $table->text('notes')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'order_lines');
    }
};
