<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'fulfilment_trackings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('fulfilment_id')->constrained($this->prefix.'fulfilments')->cascadeOnDelete();
            $table->string('carrier')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'fulfilment_trackings');
    }
};
