<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOptionsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'product_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'product_options');
    }
}
