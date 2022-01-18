<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartsEndsAtToChannelablesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'channelables', function (Blueprint $table) {
            $table->renameColumn('published_at', 'starts_at');
            $table->dateTime('ends_at')->after('published_at')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'channelables', function ($table) {
            $table->renameColumn('starts_at', 'published_at');
            $table->dropColumn('ends_at');
        });
    }
}
