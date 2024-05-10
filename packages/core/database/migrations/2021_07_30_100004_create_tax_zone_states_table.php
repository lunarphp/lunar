<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateTaxZoneStatesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'tax_zone_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_zone_id')->nullable()->constrained($this->prefix.'tax_zones');
            $table->foreignId('state_id')->nullable()->constrained($this->prefix.'states');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'tax_zone_states');
    }
}
