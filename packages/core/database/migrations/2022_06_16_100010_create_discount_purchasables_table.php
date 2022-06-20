<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountPurchasablesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix . 'discount_purchasables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained($this->prefix . 'discounts')->cascadeOnDelete();
            $table->morphs('purchasable', 'purchasable_idx');
            $table->string('type')->default('condition')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix . 'discount_purchasables');
    }
}
