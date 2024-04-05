<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddAndSetSharedToProductOptionsTable extends Migration
{
    public $withinTransaction = true;

    public function up()
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->boolean('shared')->after('handle')->default(false)->index();
        });

        \Lunar\Facades\DB::table($this->prefix.'product_options')->update([
            'shared' => true,
        ]);
    }

    public function down()
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->dropIndex($this->prefix.'product_options_shared_index');
            $table->dropColumn('shared');
        });
    }
}
