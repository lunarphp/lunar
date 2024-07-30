<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'shipping_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('shipping_method_id')->constrained(
                $this->prefix.'shipping_methods'
            );
            $table->foreignId('shipping_zone_id')->constrained(
                $this->prefix.'shipping_zones'
            );
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'shipping_rates');
    }
};
