<?php

namespace Lunar\Database\State;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Lunar\Models\Brand;
use Lunar\Models\Product;

class EnsureBrandsAreUpgraded
{
    public function prepare()
    {
        $prefix = config('lunar.database.table_prefix');

        $hasBrandsTable = Schema::hasTable("{$prefix}brands");
        $hasProductsTable = Schema::hasTable("{$prefix}products");

        if ($hasBrandsTable || ! $hasProductsTable) {
            return;
        }

        $legacyBrands = Product::query()->pluck('brand', 'id')->filter();

        if ($legacyBrands->isEmpty()) {
            return;
        }

        $brands = [];

        foreach ($legacyBrands as $productId => $brand) {
            if (empty($brands[$brand])) {
                $this->legacyBrands[$brand] = [];
            }

            $brands[$brand][] = $productId;
        }

        Storage::disk('local')->put('legacy_brands.json', json_encode($brands));
    }

    public function run()
    {
        if (! $this->canRun() || ! $this->shouldRun()) {
            return;
        }

        $brands = Storage::disk('local')->get('legacy_brands.json');

        if ($brands) {
            $brands = json_decode($brands);

            foreach ($brands as $brandName => $productIds) {
                $brand = Brand::firstOrCreate([
                    'name' => $brandName,
                ]);

                Product::whereIn('id', $productIds)->update([
                    'brand_id' => $brand->id,
                ]);
            }
        }

        Storage::disk('local')->delete('legacy_brands.json');
    }

    protected function canRun()
    {
        $prefix = config('lunar.database.table_prefix');

        return Schema::hasTable("{$prefix}brands");
    }

    protected function shouldRun()
    {
        return ! Product::has('brand')->count();
    }
}
