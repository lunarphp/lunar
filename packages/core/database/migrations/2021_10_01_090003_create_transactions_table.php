<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('parent_transaction_id')->nullable()->constrained($this->prefix.'transactions');
            $table->foreignId('order_id')->constrained($this->prefix.'orders');
            $table->boolean('success')->index();
            $table->enum('type', ['refund', 'intent', 'capture'])->index()->default('capture');
            $table->string('driver');
            $table->integer('amount')->unsigned()->index();
            $table->string('reference')->index();
            $table->string('status');
            $table->string('notes')->nullable();
            $table->string('card_type', 25)->index();
            $table->string('last_four', 4)->nullable();
            $table->json('meta')->nullable();
            $table->dateTime('captured_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'transactions');
    }
}
