<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateCustomerGroupShippingMethodTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'customer_group_shipping_method', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('customer_group_id')->constrained(
                $this->prefix.'customer_groups'
            );
            $table->foreignId('shipping_method_id')->constrained(
                $this->prefix.'shipping_methods'
            );
            $table->scheduling();
            $table->boolean('visible')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'customer_group_shipping_method');
    }
}
