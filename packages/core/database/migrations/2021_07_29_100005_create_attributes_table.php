<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->prefix.'attributes', function (Blueprint $table) {
            $table->id();
            $table->string('attribute_type')->index();
            $table->foreignId('attribute_group_id')->constrained($this->prefix.'attribute_groups');
            $table->integer('position')->index();
            $table->json('name');
            $table->string('handle');
            $table->string('section');
            $table->string('type')->index();
            $table->boolean('required');
            $table->string('default_value')->nullable();
            $table->json('configuration');
            $table->boolean('system');
            $table->timestamps();

            $table->unique(['attribute_type', 'handle']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->prefix.'attributes');
    }
}
