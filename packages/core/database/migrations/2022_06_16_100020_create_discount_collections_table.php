<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateDiscountCollectionsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'discount_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained($this->prefix.'discounts')->cascadeOnDelete();
            $table->foreignId('collection_id')->constrained($this->prefix.'collections')->cascadeOnDelete();
            $table->string('type')->default('condition')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'discount_collections');
    }
}
