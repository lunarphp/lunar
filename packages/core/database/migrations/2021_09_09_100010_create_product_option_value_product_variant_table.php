<?php

use Lunar\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOptionValueProductVariantTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'product_option_value_product_variant', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('value_id')->constrained($this->prefix.'product_option_values');
            $table->foreignId('variant_id')->constrained($this->prefix.'product_variants');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'product_option_value_product_variant');
    }
}
