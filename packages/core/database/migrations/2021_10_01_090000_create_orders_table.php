<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->userForeignKey(nullable: true);
            $table->foreignId('channel_id')->constrained($this->prefix.'channels');
            $table->string('status')->index();
            $table->string('reference')->nullable()->unique();
            $table->string('customer_reference')->nullable();
            $table->integer('sub_total')->unsigned()->index();
            $table->integer('discount_total')->default(0)->unsigned()->index();
            $table->integer('shipping_total')->default(0)->unsigned()->index();
            $table->json('tax_breakdown');
            $table->integer('tax_total')->unsigned()->index();
            $table->integer('total')->unsigned()->index();
            $table->text('notes')->nullable();
            $table->string('currency_code', 3);
            $table->string('compare_currency_code', 3)->nullable();
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->dateTime('placed_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'orders');
    }
};
