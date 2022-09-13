<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateCustomerGroupProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->prefix.'customer_group_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_group_id')->constrained($this->prefix.'customer_groups');
            $table->foreignId('product_id')->constrained($this->prefix.'products');
            $table->scheduling();
            $table->boolean('visible')->default(true)->index();
            $table->boolean('purchasable')->default(true)->index();
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
        Schema::dropIfExists($this->prefix.'customer_group_product');
    }
}
