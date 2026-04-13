<?php

use Illuminate\Support\Facades\DB;
use Lunar\Base\Migration;
use Lunar\Models\Product;
use Lunar\Shipping\Models\ShippingRate;

return new class extends Migration
{
    public function up()
    {
        DB::table($this->prefix.'prices')
            ->where('priceable_type', '=', ShippingRate::class)
            ->update([
                'priceable_type' => 'shipping_rate',
            ]);

        DB::table($this->prefix.'shipping_exclusions')
            ->where('purchasable_type', '=', Product::class)
            ->update([
                'purchasable_type' => 'product',
            ]);
    }

    public function down()
    {
        DB::table($this->prefix.'prices')
            ->where('priceable_type', '=', 'shipping_rate')
            ->update([
                'priceable_type' => ShippingRate::class,
            ]);

        DB::table($this->prefix.'shipping_exclusions')
            ->where('purchasable_type', '=', 'product')
            ->update([
                'purchasable_type' => Product::class,
            ]);
    }
};
