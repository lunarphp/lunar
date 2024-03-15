<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

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
            $table->json('description');
            $table->string('handle');
            $table->string('section')->nullable();
            $table->string('type')->index();
            $table->boolean('required');
            $table->string('default_value')->nullable();
            $table->json('configuration');
            $table->boolean('system');
            $table->boolean('searchable')->default(true)->index();
            $table->boolean('filterable')->default(false)->index();
            $table->string('validation_rules')->nullable();
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
