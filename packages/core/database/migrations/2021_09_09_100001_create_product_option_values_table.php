<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateProductOptionValuesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'product_option_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('product_option_id')->constrained($this->prefix.'product_options');
            $table->json('name');
            $table->integer('position')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'product_option_values');
    }
}
