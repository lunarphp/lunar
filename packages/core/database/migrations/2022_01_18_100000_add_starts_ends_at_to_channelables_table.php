<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddStartsEndsAtToChannelablesTable extends Migration
{
    public function up()
    {
        /**
         * SQLite will only allow one per transaction when modifying columns.
         */
        Schema::table($this->prefix.'channelables', function (Blueprint $table) {
            $table->renameColumn('published_at', 'starts_at');
        });

        Schema::table($this->prefix.'channelables', function (Blueprint $table) {
            $table->dateTime('ends_at')->after('starts_at')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'channelables', function ($table) {
            $table->renameColumn('starts_at', 'published_at');
        });

        Schema::table($this->prefix.'channelables', function ($table) {
            $table->dropIndex(['ends_at']);
            $table->dropColumn('ends_at');
        });
    }
}
