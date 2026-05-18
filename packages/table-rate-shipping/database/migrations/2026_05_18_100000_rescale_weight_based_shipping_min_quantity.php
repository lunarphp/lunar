<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->tablesExist()) {
            return;
        }

        $weightMethodIds = DB::table($this->prefix.'shipping_methods')
            ->where('data->charge_by', 'weight')
            ->pluck('id');

        if ($weightMethodIds->isEmpty()) {
            return;
        }

        $shippingRateIds = DB::table($this->prefix.'shipping_rates')
            ->whereIn('shipping_method_id', $weightMethodIds)
            ->pluck('id');

        if ($shippingRateIds->isEmpty()) {
            return;
        }

        DB::table($this->prefix.'prices')
            ->where('priceable_type', 'shipping_rate')
            ->whereIn('priceable_id', $shippingRateIds)
            ->where('min_quantity', '>', 1)
            ->orderBy('id')
            ->chunkById(100, function ($prices) {
                foreach ($prices as $price) {
                    DB::table($this->prefix.'prices')
                        ->where('id', $price->id)
                        ->update([
                            'min_quantity' => (int) ($price->min_quantity / 100),
                        ]);
                }
            });
    }

    public function down(): void
    {
        //
    }

    protected function tablesExist(): bool
    {
        return Schema::hasTable($this->prefix.'shipping_methods')
            && Schema::hasTable($this->prefix.'shipping_rates')
            && Schema::hasTable($this->prefix.'prices');
    }
};
