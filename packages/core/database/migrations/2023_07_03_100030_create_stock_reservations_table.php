<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateStockReservationsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->morphs('stockable');
            $table->foreignId('variant_id')->constrained($this->prefix.'product_variants');
            $table->unsignedInteger('quantity')->unsigned()->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'stock_reservations');
    }
}
