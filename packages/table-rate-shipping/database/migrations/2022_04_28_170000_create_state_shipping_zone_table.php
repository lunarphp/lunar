<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateStateShippingZoneTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'state_shipping_zone', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('state_id')->constrained(
                $this->prefix.'states'
            );
            $table->foreignId('shipping_zone_id')->constrained(
                $this->prefix.'shipping_zones'
            );
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'state_shipping_zone');
    }
}
