<?php

namespace Lunar\Database\State;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Models\Product;

class EnsureBrandsAreUpgraded
{
    public function prepare()
    {
        $prefix = config('lunar.database.table_prefix');

        $hasBrandsTable = Schema::hasTable("{$prefix}brands");
        $hasProductsTable = Schema::hasTable("{$prefix}products");

        if ($hasBrandsTable || ! $hasProductsTable || ! Language::count()) {
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

        Storage::put('tmp/state/legacy_brands.json', json_encode($brands));
    }

    public function run()
    {
        if (! $this->canRun() || ! $this->shouldRun()) {
            return;
        }

        $brands = null;

        try {
            $brands = Storage::get('tmp/state/legacy_brands.json');
        } catch (FileNotFoundException $e) {
        }

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

        Storage::disk('local')->delete('tmp/state/legacy_brands.json');
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
