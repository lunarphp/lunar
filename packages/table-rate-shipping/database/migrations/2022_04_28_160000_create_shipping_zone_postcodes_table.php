<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateShippingZonePostcodesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'shipping_zone_postcodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('shipping_zone_id')->constrained(
                $this->prefix.'shipping_zones'
            );
            $table->string('postcode')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'shipping_zone_postcodes');
    }
}
