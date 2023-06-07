<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddCartIdToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->foreignId('cart_id')->after('user_id')->nullable()->constrained($this->prefix.'carts')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->dropForeign(['cart_id']);
            $table->dropColumn('cart_id');
        });
    }
}
