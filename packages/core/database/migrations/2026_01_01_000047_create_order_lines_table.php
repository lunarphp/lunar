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
            $table->ulid('public_id')->unique();
            $table->foreignId('order_id')->constrained($this->prefix.'orders');
            $table->nullableMorphs('purchasable');
            $table->string('type')->index();
            $table->boolean('requires_shipping')->default(false)->index()->comment('Needs physical delivery; stamped from the purchasable isShippable()');
            $table->boolean('requires_fulfilment')->default(false)->index()->comment('Needs fulfilling at all; superset of requires_shipping, drives the fulfilment rollup');
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
