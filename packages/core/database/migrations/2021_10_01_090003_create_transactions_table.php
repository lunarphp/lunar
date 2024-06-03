<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('order_id')->constrained($this->prefix.'orders');
            $table->boolean('success')->index();
            $table->boolean('refund')->default(false)->index();
            $table->string('driver');
            $table->integer('amount')->unsigned()->index();
            $table->string('reference')->index();
            $table->string('status');
            $table->string('notes')->nullable();
            $table->string('card_type', 25)->index();
            $table->smallInteger('last_four');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'transactions');
    }
};
