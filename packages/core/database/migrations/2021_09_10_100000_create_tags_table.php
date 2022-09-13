<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateTagsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('value')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'tags');
    }
}
