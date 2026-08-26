<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;
use Lunar\DataTypes\ShippingOption;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'order_lines', function (Blueprint $table) {
            $table->string('purchasable_type')->nullable()->change();
            $table->unsignedBigInteger('purchasable_id')->nullable()->change();
        });

        // Shipping lines carried a fake morph (ShippingOption is a DTO, not a
        // model) that could never resolve; null it so reads no longer fatal.
        DB::table($this->prefix.'order_lines')
            ->where('type', 'shipping')
            ->where('purchasable_type', ShippingOption::class)
            ->update([
                'purchasable_type' => null,
                'purchasable_id' => null,
            ]);
    }

    public function down(): void
    {
        DB::table($this->prefix.'order_lines')
            ->where('type', 'shipping')
            ->whereNull('purchasable_type')
            ->update([
                'purchasable_type' => ShippingOption::class,
                'purchasable_id' => 1,
            ]);

        Schema::table($this->prefix.'order_lines', function (Blueprint $table) {
            $table->string('purchasable_type')->nullable(false)->change();
            $table->unsignedBigInteger('purchasable_id')->nullable(false)->change();
        });
    }
};
