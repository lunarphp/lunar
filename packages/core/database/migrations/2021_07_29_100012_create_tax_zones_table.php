<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateTaxZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->prefix.'tax_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zone_type')->index();
            $table->string('price_display');
            $table->boolean('active')->index();
            $table->boolean('default')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->prefix.'tax_zones');
    }
}
