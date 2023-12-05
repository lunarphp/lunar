<?php

use Lunar\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderShippingZoneTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'order_shipping_zone', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('order_id')->constrained(
                $this->prefix.'orders'
            );
            $table->foreignId('shipping_zone_id')->constrained(
                $this->prefix.'shipping_zones'
            );
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'order_shipping_zone');
    }
}
