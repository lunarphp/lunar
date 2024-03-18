<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateProductOptionsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'product_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('name');
            $table->json('label')->nullable();
            $table->string('handle')->nullable();
            $table->boolean('shared')->default(true)->index();
            // @todo check if we really need should drop this column RemovePositionFromProductOptionsTable?
            // $table->integer('position')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'product_options');
    }
}
