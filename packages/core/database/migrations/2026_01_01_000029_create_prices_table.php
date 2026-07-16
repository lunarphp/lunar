<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'prices', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('customer_group_id')->nullable()->constrained($this->prefix.'customer_groups');
            $table->foreignId('currency_id')->nullable(false);
            $table->morphs('priceable');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('list_price')->nullable();
            $table->integer('min_quantity')->default(1)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'prices');
    }
};
