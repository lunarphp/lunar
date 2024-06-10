<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_group_id')->nullable()->constrained($this->prefix.'customer_groups');
            $table->foreignId('currency_id')->nullable()->constrained($this->prefix.'currencies');
            $table->morphs('priceable');
            $table->integer('price')->unsigned()->index();
            $table->integer('compare_price')->unsigned()->nullable();
            $table->integer('tier')->default(1)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'prices');
    }
};
