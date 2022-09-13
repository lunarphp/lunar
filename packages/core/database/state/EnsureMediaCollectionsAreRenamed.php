<?php

namespace GetCandy\Database\State;

use GetCandy\Models\Brand;
use GetCandy\Models\Collection;
use GetCandy\Models\Product;
use Illuminate\Support\Facades\DB;

class EnsureMediaCollectionsAreRenamed
{
    public function run()
    {
        if (! $this->shouldRun()) {
            return;
        }

        $this->getOutdatedMediaQuery()->update(['collection_name' => 'images']);
    }

    protected function shouldRun()
    {
        return $this->getOutdatedMediaQuery()->count();
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
