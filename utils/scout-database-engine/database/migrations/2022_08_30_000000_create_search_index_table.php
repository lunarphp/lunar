<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Lunar\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_index', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->string('index')->index();
            $table->string('field')->index();
            $table->text('content');
            $table->timestamps();

            if (DB::getDriverName() !== 'sqlite') {
                $table->fullText('content');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_index');
    }
};
