<?php

namespace Lunar\Panel\Dashboard\Widgets;

use Illuminate\Support\Facades\DB;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\OrderMetrics;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Support\Position;

class TopProductsWidget extends Widget
{
    public function __construct(protected OrderMetrics $metrics) {}

    public function key(): string
    {
        return 'top-products';
    }

    public function component(): string
    {
        return 'TopProductsWidget';
    }

    public function label(): string
    {
        return __('panel::dashboard.widget_top_products_label');
    }

    public function description(): ?string
    {
        return __('panel::dashboard.widget_top_products_description');
    }

    public function icon(): ?string
    {
        return 'tag';
    }

    public function permission(): ?string
    {
        return 'catalog:manage-products';
    }

    public function position(): Position
    {
        return Position::priority(40);
    }

    public function data(DashboardRange $range): array
    {
        $prefix = config('lunar.database.table_prefix');

        // Join lines to the placed-order set and sum revenue/units per variant
        // in the database, keeping only the top five — the store can hold tens
        // of thousands of lines in a window.
        $placedOrders = $this->metrics
            ->placedOrders($range->start(), $range->end())
            ->toBase()
            ->select('id', 'exchange_rate');

        $revenue = $this->metrics->valueInDefaultCurrency('ol.sub_total', 'o.exchange_rate');

        $top = DB::table($prefix.'order_lines as ol')
            ->joinSub($placedOrders, 'o', 'o.id', '=', 'ol.order_id')
            ->where('ol.purchasable_type', ProductVariant::morphName())
            ->groupBy('ol.purchasable_id')
            ->selectRaw('ol.purchasable_id, COALESCE(SUM('.$revenue.'), 0) as revenue, COALESCE(SUM(ol.quantity), 0) as units')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $variants = ProductVariant::query()
            ->with(['product:id,product_type_id,status,name', 'product.thumbnail'])
            ->findMany($top->pluck('purchasable_id'));

        $products = [];

        foreach ($top as $row) {
            $variant = $variants->find($row->purchasable_id);

            if ($variant === null) {
                continue;
            }

            $products[] = [
                'name' => $variant->product?->translate('name') ?? $variant->sku,
                'sku' => $variant->sku,
                'thumbnail' => $variant->product?->thumbnail?->getAvailableUrl(['small']),
                'units' => (int) $row->units,
                'revenue' => $this->metrics->format((int) round((float) $row->revenue)),
                'url' => $variant->product ? route('panel.products.edit', $variant->product) : null,
            ];
        }

        return ['products' => $products];
    }
}
