<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddPositionToMediaProductVariantTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'media_product_variant', function (Blueprint $table) {
            $table->smallInteger('position')->after('primary')->default(1)->index();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'media_product_variant', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropColumn('position');
        });
    }
}
