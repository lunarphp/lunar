<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateBrandsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('attribute_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'brands');
    }
}
