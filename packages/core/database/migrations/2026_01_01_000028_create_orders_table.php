<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ulid('public_id')->unique();
            $table->userForeignKey(nullable: true);
            $table->foreignId('channel_id')->constrained($this->prefix.'channels');
            $table->string('payment_status')->default('pending')->index();
            $table->string('fulfilment_status')->default('unfulfilled')->index();
            $table->string('reference')->nullable()->unique();
            $table->string('customer_reference')->nullable();
            $table->unsignedBigInteger('sub_total');
            $table->unsignedBigInteger('discount_total');
            $table->unsignedBigInteger('shipping_total');
            $table->jsonb('tax_breakdown');
            $table->unsignedBigInteger('tax_total');
            $table->unsignedBigInteger('total');
            $table->text('notes')->nullable();
            $table->string('currency_code', 3);
            $table->string('compare_currency_code', 3)->nullable();
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->dateTime('placed_at')->nullable()->index();
            $table->dateTime('closed_at')->nullable()->index();
            $table->dateTime('cancelled_at')->nullable()->index();
            $table->string('cancel_reason')->nullable();
            $table->text('cancel_note')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
            $table->foreignId('customer_id')->nullable()->constrained($this->prefix.'customers');
            $table->boolean('new_customer')->default(false)->index();
            $table->jsonb('discount_breakdown')->nullable();
            $table->jsonb('shipping_breakdown')->nullable();
            // FK to lunar_carts added in a follow-up migration (circular orders <-> carts dependency).
            $table->unsignedBigInteger('cart_id')->nullable()->index();
            $table->string('fingerprint')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'orders');
    }
};
