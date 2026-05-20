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

        // Only rescale rows that are still in legacy "kg × 100" storage. The
        // `min_quantity % 100 = 0` guard makes a second run a no-op: already
        // rescaled values (5, 10, 25…) and any raw kg values entered via the
        // new UI are skipped. Legitimate breakpoints that happen to be a
        // multiple of 100 kg (e.g. 100, 200) are inherently ambiguous on first
        // run; document those rows post-deploy if they exist.
        DB::table($this->prefix.'prices')
            ->where('priceable_type', 'shipping_rate')
            ->whereIn('priceable_id', $shippingRateIds)
            ->where('min_quantity', '>', 1)
            ->whereRaw('min_quantity % 100 = 0')
            ->update([
                'min_quantity' => DB::raw('min_quantity / 100'),
            ]);
    }

    public function down(): void
    {
        // Intentionally irreversible: the original value of an already-1 row
        // is indistinguishable from a rescaled-to-1 row, so we cannot restore
        // the previous kg × 100 storage faithfully.
    }

    protected function tablesExist(): bool
    {
        return Schema::hasTable($this->prefix.'shipping_methods')
            && Schema::hasTable($this->prefix.'shipping_rates')
            && Schema::hasTable($this->prefix.'prices');
    }
};
