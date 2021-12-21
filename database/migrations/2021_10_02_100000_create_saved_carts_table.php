<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavedCartsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'saved_carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('cart_id')->nullable()->constrained($this->prefix.'carts');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'saved_carts');
    }
}
