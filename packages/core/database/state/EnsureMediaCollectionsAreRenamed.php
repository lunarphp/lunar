<?php

namespace Lunar\Database\State;

use Lunar\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Product;

class EnsureMediaCollectionsAreRenamed
{
    public function prepare()
    {
        //
    }

    public function run()
    {
        if (! $this->shouldRun()) {
            return;
        }

        $this->getOutdatedMediaQuery()->update(['collection_name' => 'images']);
    }

    protected function shouldRun()
    {
        return Schema::hasTable('media') && $this->getOutdatedMediaQuery()->count();
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getOutdatedMediaQuery()
    {
        return DB::table(app(config('media-library.media_model'))->getTable())
            ->whereIn('model_type', [Product::class, Collection::class, Brand::class])
            ->where('collection_name', 'products');
    }
}
