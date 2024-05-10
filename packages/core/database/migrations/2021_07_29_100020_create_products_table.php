<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_id')->constrained($this->prefix.'product_types');
            $table->string('status')->index();
            $table->json('attribute_data');
            $table->string('brand')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'products');
    }
}
