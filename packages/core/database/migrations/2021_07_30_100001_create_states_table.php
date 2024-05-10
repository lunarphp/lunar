<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateStatesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained($this->prefix.'countries');
            $table->string('name');
            $table->string('code');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'states');
    }
}
