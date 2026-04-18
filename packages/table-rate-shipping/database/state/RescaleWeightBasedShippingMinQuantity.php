<?php

namespace Lunar\Shipping\Database\State;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RescaleWeightBasedShippingMinQuantity
{
    public function prepare(): void
    {
        //
    }

    public function run(): void
    {
        if (! $this->canRun()) {
            return;
        }

        $prefix = config('lunar.database.table_prefix');

        $weightMethodIds = DB::table("{$prefix}shipping_methods")
            ->whereJsonContains('data->charge_by', 'weight')
            ->pluck('id');

        if ($weightMethodIds->isEmpty()) {
            return;
        }

        $shippingRateIds = DB::table("{$prefix}shipping_rates")
            ->whereIn('shipping_method_id', $weightMethodIds)
            ->pluck('id');

        if ($shippingRateIds->isEmpty()) {
            return;
        }

        DB::table("{$prefix}prices")
            ->where('priceable_type', 'shipping_rate')
            ->whereIn('priceable_id', $shippingRateIds)
            ->where('min_quantity', '>', 1)
            ->orderBy('id')
            ->chunk(100, function ($prices) use ($prefix) {
                foreach ($prices as $price) {
                    DB::table("{$prefix}prices")
                        ->where('id', $price->id)
                        ->update([
                            'min_quantity' => (int) ($price->min_quantity / 100),
                        ]);
                }
            });
    }

    protected function canRun(): bool
    {
        $prefix = config('lunar.database.table_prefix');

        return Schema::hasTable("{$prefix}shipping_methods")
            && Schema::hasTable("{$prefix}shipping_rates")
            && Schema::hasTable("{$prefix}prices");
    }
}
