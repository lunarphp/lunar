<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('exchange_rate', 10, 4);
            $table->string('format');
            $table->string('decimal_point');
            $table->string('thousand_point');
            $table->integer('decimal_places')->default(2)->index();
            $table->boolean('enabled')->default(false)->index();
            $table->boolean('default')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'currencies');
    }
};
