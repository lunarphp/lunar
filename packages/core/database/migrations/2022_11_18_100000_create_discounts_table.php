<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('handle')->unique();
            $table->string('coupon')->nullable()->unique();
            $table->string('type')->index();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->nullable()->index();
            $table->integer('uses')->unsigned()->default(0)->index();
            $table->mediumInteger('max_uses')->unsigned()->nullable();
            $table->mediumInteger('priority')->unsigned()->index()->default(1);
            $table->boolean('stop')->default(false)->index();
            $table->string('restriction')->index()->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'discounts');
    }
};
