<?php

namespace Lunar\Hub\Tables\Builders;

use Illuminate\Support\Facades\DB;
use Lunar\Hub\Tables\TableBuilder;
use Lunar\Models\Brand;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductsTableBuilder extends TableBuilder
{
    /**
     * Return the query data.
     *
     * @param  string|null  $searchTerm
     * @param  array  $filters
     * @param  string  $sortField
     * @param  string  $sortDir
     * @return LengthAwarePaginator
     */
    public function getData(): iterable
    {
        $productTable = (new Product())->getTable();
        $productVariantTable = (new ProductVariant())->getTable();
        $productTypeTable = (new ProductType())->getTable();
        $brandTable = (new Brand())->getTable();
        $mediaTable = (new Media())->getTable();

        $query = Product::select([
            "{$productTable}.*",
            "{$mediaTable}.id AS media_id",
            "{$mediaTable}.model_id AS media_model_id",
            "{$mediaTable}.model_type AS media_model_type",
            "{$mediaTable}.file_name AS media_file_name",
            "{$mediaTable}.conversions_disk AS media_conversions_disk",
            DB::raw(<<<SQL
                (
                    CASE
                        WHEN (
                            SELECT
                                COUNT(*)
                            FROM
                                {$productVariantTable}
                            WHERE
                                {$productVariantTable}.product_id = {$productTable}.id
                        ) > 1 THEN 'Multiple'
                        ELSE (
                            SELECT
                                sku
                            FROM
                                {$productVariantTable}
                            WHERE
                                {$productVariantTable}.product_id = {$productTable}.id
                            LIMIT
                                1
                        )
                    END
                ) AS sku,
                (
                    SELECT
                        SUM(stock)
                    FROM
                        {$productVariantTable}
                    WHERE
                        {$productVariantTable}.product_id = {$productTable}.id
                ) AS stock,
                (
                    SELECT
                        name
                    FROM
                        {$productTypeTable}
                    WHERE
                        {$productTypeTable}.id = {$productTable}.product_type_id
                ) AS product_type,
                (
                    SELECT
                        name
                    FROM
                        {$brandTable}
                    WHERE
                        {$brandTable}.id = {$productTable}.brand_id
                ) AS brand
            SQL),
        ])
            ->leftJoin('media', function ($join) {
                $join->on('media.model_id', '=', 'lunar_products.id')
                    ->where('media.model_type', '=', Product::class)
                    ->where('custom_properties->primary', true);
            })
            ->orderBy($this->sortField, $this->sortDir)
            ->withTrashed();

        if ($this->searchTerm) {
            $query->whereIn('id', Product::search($this->searchTerm)->keys());
        }

        $filters = collect($this->queryStringFilters)->filter(function ($value) {
            return (bool) $value;
        });

        foreach ($this->queryExtenders as $qe) {
            call_user_func($qe, $query, $this->searchTerm, $filters);
        }

        // Get the table filters we want to apply.
        $tableFilters = $this->getFilters()->filter(function ($filter) use ($filters) {
            return $filters->has($filter->field);
        });

        foreach ($tableFilters as $filter) {
            call_user_func($filter->getQuery(), $filters, $query);
        }

        return $query->paginate($this->perPage);
    }
}
