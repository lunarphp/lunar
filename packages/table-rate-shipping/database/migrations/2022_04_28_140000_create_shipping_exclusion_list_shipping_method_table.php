<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateShippingExclusionListShippingMethodTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'exclusion_list_shipping_method', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('exclusion_id')->constrained(
                $this->prefix.'shipping_exclusion_lists'
            );
            $table->foreignId('method_id')->constrained(
                $this->prefix.'shipping_methods'
            );
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'shipping_exclusion_list_shipping_method');
    }
}
