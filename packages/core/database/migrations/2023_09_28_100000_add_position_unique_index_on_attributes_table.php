<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table($this->prefix . 'attributes', function (Blueprint $table) {
            $table->unique(
                ['attribute_type', 'attribute_group_id', 'position'], 
                $this->prefix . 'attributes_unique_position'
            );
        });
    }

    public function down()
    {
        Schema::table($this->prefix . 'attributes', function (Blueprint $table) {
            $table->dropUnique($this->prefix . 'attributes_unique_position');
        });
    }
};