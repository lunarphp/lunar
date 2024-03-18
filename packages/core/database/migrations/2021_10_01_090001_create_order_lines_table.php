<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateOrderLinesTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'order_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('order_id')->constrained($this->prefix.'orders');
            $table->morphs('purchasable');
            $table->string('type')->index();
            $table->string('description');
            $table->string('option')->nullable();
            $table->string('identifier')->index();
            $table->unsignedInteger('quantity')->unsigned();
            $table->unsignedBigInteger('unit_price')->index();
            $table->unsignedBigInteger('unit_quantity')->default(1)->index();
            $table->unsignedBigInteger('sub_total')->index();
            $table->unsignedBigInteger('discount_total')->default(0)->index();
            $table->unsignedBigInteger('tax_total')->unsigned()->index();
            $table->unsignedBigInteger('total')->unsigned()->index();
            $table->json('tax_breakdown');
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'order_lines');
    }
}
