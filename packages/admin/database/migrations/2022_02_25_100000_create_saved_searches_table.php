<?php

use Lunar\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavedSearchesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'saved_searches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('staff_id')->constrained(
                $this->prefix.'staff'
            )->cascadeOnDelete();
            $table->string('name');
            $table->string('component')->index();
            $table->string('term')->nullable();
            $table->json('filters')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'saved_searches');
    }
}
