<?php

namespace GetCandy\Database\State;

use GetCandy\Models\Brand;
use GetCandy\Models\Product;
use Illuminate\Support\Facades\Schema;

class EnsureBrandsAreUpgraded
{
    public function run()
    {
        if (! $this->canRun() || ! $this->shouldRun()) {
            return;
        }

        $legacyBrands = Product::query()->pluck('brand', 'id');
        if ($legacyBrands->isEmpty()) {
            return;
        }

        $legacyBrands->each(function ($brand, $id) {
            /** @var Product $product */
            $product = Product::query()->findOrFail($id);
            $product->brand()->associate(Brand::query()->firstOrCreate([
                'name' => $brand,
            ]));

            $product->save();
        });

        if (Product::has('brand')->count() === $legacyBrands->count()) {
            $prefix = config('getcandy.database.table_prefix');
            Schema::dropColumns("{$prefix}products", ['brand']);
        }
    }

    protected function canRun()
    {
        $prefix = config('getcandy.database.table_prefix');

        return Schema::hasTable("{$prefix}brands");
    }

    protected function shouldRun()
    {
        return ! Product::has('brand')->count();
    }
}
