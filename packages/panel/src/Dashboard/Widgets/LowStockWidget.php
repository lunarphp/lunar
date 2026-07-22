<?php

namespace Lunar\Panel\Dashboard\Widgets;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Core\Enums\SellingPolicy;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Support\Position;

class LowStockWidget extends Widget
{
    public function key(): string
    {
        return 'low-stock';
    }

    public function component(): string
    {
        return 'LowStockWidget';
    }

    public function label(): string
    {
        return __('panel::dashboard.widget_low_stock_label');
    }

    public function description(): ?string
    {
        return __('panel::dashboard.widget_low_stock_description');
    }

    public function icon(): ?string
    {
        return 'alert';
    }

    public function permission(): ?string
    {
        return 'catalog:manage-products';
    }

    public function position(): Position
    {
        return Position::priority(80);
    }

    public function data(DashboardRange $range): array
    {
        $threshold = (int) config('lunar.panel.dashboard.low_stock_threshold', 10);

        $query = ProductVariant::query()
            ->where('stock_available', '<=', $threshold)
            // Variants sold regardless of stock have no reorder pressure.
            ->where('selling_policy', '!=', SellingPolicy::Always)
            ->whereHas('product', fn (Builder $product) => $product->whereVisible());

        $count = (clone $query)->count();

        $variants = $query
            ->with(['product:id,product_type_id,status,name'])
            ->orderBy('stock_available')
            ->limit(8)
            ->get();

        return [
            'threshold' => $threshold,
            'count' => $count,
            'variants' => $variants->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'name' => $variant->product?->translate('name') ?? $variant->sku,
                'sku' => $variant->sku,
                'stock' => (int) $variant->stock_available,
                'url' => $variant->product ? route('panel.products.edit', $variant->product) : null,
            ])->all(),
        ];
    }
}
