<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table($this->prefix . 'attribute_groups', function (Blueprint $table) {
            $table->unique(
                ['attributable_type', 'position'], 
                $this->prefix . 'attribute_groups_unique_position'
            );
        });
    }

    public function down()
    {
        Schema::table($this->prefix . 'attribute_groups', function (Blueprint $table) {
            $table->dropUnique($this->prefix . 'attribute_groups_unique_position');
        });
    }
};