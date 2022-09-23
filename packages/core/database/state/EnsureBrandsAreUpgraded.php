<?php

namespace Lunar\Database\State;

use Illuminate\Support\Facades\Schema;
use Lunar\Models\Brand;
use Lunar\Models\Product;

class EnsureBrandsAreUpgraded
{
    /**
     * The legacy brands to import.
     *
     * @var array
     */
    protected $legacyBrands = [];

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

        foreach ($legacyBrands as $productId => $brand) {
            if (empty($this->legacyBrands[$brand])) {
                $this->legacyBrands[$brand] = [];
            }

            $this->legacyBrands[$brand][] = $productId;
        }
    }

    public function run()
    {
        if (! $this->canRun() || ! $this->shouldRun()) {
            return;
        }

        foreach ($this->legacyBrands as $brandName => $productIds) {
            $brand = Brand::firstOrCreate([
                'name' => $brandName,
            ]);

            Product::whereIn('id', $productIds)->update([
                'brand_id' => $brand->id,
            ]);
        }
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
