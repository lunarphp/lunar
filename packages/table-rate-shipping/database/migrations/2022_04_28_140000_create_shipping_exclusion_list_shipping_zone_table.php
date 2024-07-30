<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'exclusion_list_shipping_zone', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('exclusion_id')->constrained(
                $this->prefix.'shipping_exclusion_lists'
            );
            $table->foreignId('shipping_zone_id')->constrained(
                $this->prefix.'shipping_zones'
            );
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'exclusion_list_shipping_zone');
    }
};
