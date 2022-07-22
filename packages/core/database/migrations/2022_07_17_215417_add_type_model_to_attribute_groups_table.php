<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeModelToAttributeGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->prefix.'attribute_groups', function (Blueprint $table) {
            $table->string('type')->after('attributable_type')->default('default');
            $table->string('source')->after('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->prefix.'attribute_groups', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('source');
        });
    }
}
