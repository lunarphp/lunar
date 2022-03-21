<?php

namespace GetCandy\Database\State;

use GetCandy\Models\Price;
use GetCandy\Models\Product;
use Illuminate\Support\Facades\Schema;

class EnsureAllProductsHavePriceSortingSet
{
    public function run()
    {
        if (! $this->canRun() || ! $this->shouldRun()) {
            return;
        }

        // Get all prices at tier 1 and force an update
        Price::where('tier', '=', 1)->chunk(1000, function ($prices) {
            foreach ($prices as $price) {
                event('eloquent.updating: '.Price::class, $price);
            }
        });
    }

    protected function canRun()
    {
        $prefix = config('getcandy.database.table_prefix');

        return Schema::hasColumn($prefix.'products', 'sorting');
    }

    protected function shouldRun()
    {
        return Product::whereNull('sorting->price_default')->count();
    }
}
