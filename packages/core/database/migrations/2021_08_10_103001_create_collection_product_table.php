<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateCollectionProductTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'collection_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained($this->prefix.'collections');
            $table->foreignId('product_id')->constrained($this->prefix.'products');
            $table->integer('position')->default(1)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'collection_product');
    }
}
