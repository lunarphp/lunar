<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountTables extends Migration
{
    public function up()
    {
        Schema::table($this->prefix . 'discounts', function (Blueprint $table) {
            $table->id();
            $table->json('handle')->unique();
            $table->json('attribute_data');
        });

        Schema::table($this->prefix . 'discount_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained($this->prefix . 'discounts');
            $table->string('driver')->index();
            $table->json('data');
        });

        Schema::table($this->prefix . 'discount_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained($this->prefix . 'discounts');
            $table->string('driver')->index();
            $table->json('data');
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix . 'discounts');
        Schema::dropIfExists($this->prefix . 'discount_conditions');
        Schema::dropIfExists($this->prefix . 'discount_rewards');
    }
}
