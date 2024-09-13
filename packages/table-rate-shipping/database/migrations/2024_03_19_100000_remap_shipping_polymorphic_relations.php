<?php

use Lunar\Base\Migration;

class RemapShippingPolymorphicRelations extends Migration
{
    public function up()
    {
        \Illuminate\Support\Facades\DB::table($this->prefix.'prices')
            ->where('priceable_type', '=', \Lunar\Shipping\Models\ShippingRate::class)
            ->update([
                'priceable_type' => 'shipping_rate',
            ]);

        \Illuminate\Support\Facades\DB::table($this->prefix.'shipping_exclusions')
            ->where('purchasable_type', '=', \Lunar\Models\Product::class)
            ->update([
                'purchasable_type' => 'product',
            ]);
    }

    public function down()
    {
        \Illuminate\Support\Facades\DB::table($this->prefix.'prices')
            ->where('priceable_type', '=', 'shipping_rate')
            ->update([
                'priceable_type' => \Lunar\Shipping\Models\ShippingRate::class,
            ]);

        \Illuminate\Support\Facades\DB::table($this->prefix.'shipping_exclusions')
            ->where('purchasable_type', '=', 'product')
            ->update([
                'purchasable_type' => \Lunar\Models\Product::class,
            ]);
    }
}
