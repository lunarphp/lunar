<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'fulfilment_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ulid('public_id')->unique();
            $table->foreignId('fulfilment_id')->constrained($this->prefix.'fulfilments')->cascadeOnDelete();
            $table->foreignId('order_line_id')->constrained($this->prefix.'order_lines')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['fulfilment_id', 'order_line_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'fulfilment_lines');
    }
};
