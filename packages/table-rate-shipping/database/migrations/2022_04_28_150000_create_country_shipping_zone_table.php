<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateCountryShippingZoneTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'country_shipping_zone', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('country_id')->constrained(
                $this->prefix.'countries'
            );
            $table->foreignId('shipping_zone_id')->constrained(
                $this->prefix.'shipping_zones'
            );
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'country_shipping_zone');
    }
}
