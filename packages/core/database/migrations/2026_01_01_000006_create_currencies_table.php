<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('exchange_rate', 10, 4);
            $table->integer('decimal_places')->default(2)->index();
            $table->boolean('enabled')->default(false)->index();
            $table->boolean('default')->default(false)->index();
            $table->timestamps();
            $table->boolean('sync_prices')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'currencies');
    }
};
