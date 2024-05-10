<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateTaxRatesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_zone_id')->nullable()->constrained($this->prefix.'tax_zones');
            $table->tinyInteger('priority')->default(1)->index()->unsigned();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'tax_rates');
    }
}
