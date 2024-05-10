<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateAttributablesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'attributables', function (Blueprint $table) {
            $table->id();
            $table->morphs('attributable');
            $table->foreignId('attribute_id')->constrained($this->prefix.'attributes');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'attributables');
    }
}
