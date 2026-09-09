<?php

namespace Lunar\Shipping\Panel\Support;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Product;
use Lunar\Shipping\Models\ShippingExclusionList;

/**
 * The excluded products of a list as picker chips, one shape shared by the
 * edit screen and the search endpoint so a product reads the same in both.
 */
class ExclusionRows
{
    /**
     * @return array<int, array{id: int, label: string, hint: ?string}>
     */
    public function forList(ShippingExclusionList $list): array
    {
        return Product::query()
            ->with('variants:id,product_id,sku')
            ->whereIn('id', $this->productIds($list))
            ->get()
            ->map(fn (Product $product) => $this->row($product))
            ->sortBy('label')
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, label: string, hint: ?string}
     */
    public function row(Product $product): array
    {
        return [
            'id' => $product->id,
            'label' => (string) ($product->translate('name') ?? ''),
            'hint' => $product->variants->first()?->sku,
        ];
    }

    /**
     * @return Collection<int, int>
     */
    public function productIds(ShippingExclusionList $list): Collection
    {
        return $list->exclusions()
            ->where('purchasable_type', Product::morphName())
            ->pluck('purchasable_id')
            ->map(fn ($id) => (int) $id);
    }
}
