<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ulid('public_id')->unique();
            $table->foreignId('order_id')->constrained($this->prefix.'orders');
            $table->boolean('success')->index();
            $table->string('driver');
            $table->integer('amount')->unsigned()->index();
            $table->string('reference')->index();
            $table->string('status');
            $table->string('notes')->nullable();
            $table->string('card_type')->nullable();
            $table->string('last_four')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
            $table->foreignId('parent_transaction_id')->nullable()->constrained($this->prefix.'transactions');
            $table->dateTime('captured_at')->nullable()->index();
            $table->enum('type', ['refund', 'intent', 'capture'])->index()->default('capture');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'transactions');
    }
};
