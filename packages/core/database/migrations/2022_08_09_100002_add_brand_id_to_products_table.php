<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;
use Lunar\Facades\DB;

class AddBrandIdToProductsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'products', function (Blueprint $table) {
            $table->foreignId('brand_id')->after('id')
                ->nullable()
                ->constrained($this->prefix.'brands');
        });

        Schema::table($this->prefix.'products', function (Blueprint $table) {
            if (Schema::hasIndex($this->prefix.'products', ['brand'])) {
                $table->dropIndex($this->prefix . 'products_brand_index');
            }
            $table->dropColumn('brand');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'products', function ($table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['brand_id']);
            }
            $table->dropColumn('brand_id');
        });
    }
}
