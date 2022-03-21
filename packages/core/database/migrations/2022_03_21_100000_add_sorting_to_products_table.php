<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortingToProductsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'products', function (Blueprint $table) {
            $table->json('sorting')->after('brand');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'products', function (Blueprint $table) {
            $table->dropColumn('sorting');
        });
    }
}
