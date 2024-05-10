<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateAttributeGroupsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->string('attributable_type')->index();
            $table->json('name');
            $table->string('handle')->unique();
            $table->integer('position')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'attribute_groups');
    }
}
