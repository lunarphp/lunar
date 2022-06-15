<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountTables extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'discounts', function (Blueprint $table) {
            $table->id();
            $table->string('handle')->unique();
            $table->json('attribute_data');
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->nullable()->index();
            $table->mediumInteger('max_uses')->unsigned()->nullable();
            $table->mediumInteger('priority')->unsigned()->index()->default(1);
            $table->boolean('stop')->default(false)->index();
            $table->timestamps();
        });

        Schema::create($this->prefix.'discount_rulesets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained($this->prefix.'discounts');
            $table->string('criteria')->index();
            $table->timestamps();
        });

        Schema::create($this->prefix.'discount_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_ruleset_id')->constrained($this->prefix.'discount_rulesets');
            $table->string('driver')->index();
            $table->json('data');
            $table->timestamps();
        });

        Schema::create($this->prefix.'discount_purchasables', function (Blueprint $table) {
            $table->id();
            $table->morphs('purchasable', 'purchasable_idx');
            $table->morphs('type');
            $table->timestamps();
        });

        Schema::create($this->prefix.'discount_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained($this->prefix.'discounts');
            $table->string('driver')->index();
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'discounts');
        Schema::dropIfExists($this->prefix.'discount_conditions');
        Schema::dropIfExists($this->prefix.'discount_rewards');
    }
}
