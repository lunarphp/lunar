<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddTypeToCollectionDiscountTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'collection_discount', function (Blueprint $table) {
            $table->string('type', 20)->after('collection_id')->default('limitation');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'collection_discount', function ($table) {
            $table->dropColumn('type');
        });        
    }
}
