<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateDiscountCollectionsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'collection_discount', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained($this->prefix.'discounts')->cascadeOnDelete();
            $table->foreignId('collection_id')->constrained($this->prefix.'collections')->cascadeOnDelete();
            $table->string('type', 20)->default('limitation');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'collection_discount');
    }
}
