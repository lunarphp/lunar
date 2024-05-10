<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateChannelablesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'channelables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained($this->prefix.'channels');
            $table->morphs('channelable');
            $table->boolean('enabled')->default(false);
            $table->datetime('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'channelables');
    }
}
