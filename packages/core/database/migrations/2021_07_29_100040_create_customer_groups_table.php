<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateCustomerGroupsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('handle')->unique();
            $table->boolean('default')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'customer_groups');
    }
}
