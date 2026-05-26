<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;
use Lunar\Core\Models\Product;
use Lunar\Shipping\Models\ShippingRate;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable($this->prefix.'prices')) {
            DB::table($this->prefix.'prices')
                ->where('priceable_type', '=', ShippingRate::class)
                ->update([
                    'priceable_type' => 'shipping_rate',
                ]);
        }

        if (Schema::hasTable($this->prefix.'shipping_exclusions')) {
            DB::table($this->prefix.'shipping_exclusions')
                ->where('purchasable_type', '=', Product::class)
                ->update([
                    'purchasable_type' => 'product',
                ]);
        }
    }

    public function down()
    {
        if (Schema::hasTable($this->prefix.'prices')) {
            DB::table($this->prefix.'prices')
                ->where('priceable_type', '=', 'shipping_rate')
                ->update([
                    'priceable_type' => ShippingRate::class,
                ]);
        }

        if (Schema::hasTable($this->prefix.'shipping_exclusions')) {
            DB::table($this->prefix.'shipping_exclusions')
                ->where('purchasable_type', '=', 'product')
                ->update([
                    'purchasable_type' => Product::class,
                ]);
        }
    }
};
