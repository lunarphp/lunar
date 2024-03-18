<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateChannelablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->prefix.'channelables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained($this->prefix.'channels');
            $table->morphs('channelable');
            $table->boolean('enabled')->default(false);
            $table->datetime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->prefix.'channelables');
    }
}
