<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateShippingRatesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'shipping_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('shipping_method_id')->constrained(
                $this->prefix.'shipping_methods'
            );
            $table->foreignId('shipping_zone_id')->constrained(
                $this->prefix.'shipping_zones'
            );
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'shipping_rates');
    }
}
