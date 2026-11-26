<?php

namespace Lunar\Panel\Dashboard\Widgets;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;
use Lunar\Core\States\Product\Draft;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Dashboard\WidgetSpan;
use Lunar\Panel\Support\Position;

class TasksWidget extends Widget
{
    public function key(): string
    {
        return 'tasks';
    }

    public function component(): string
    {
        return 'TasksWidget';
    }

    public function label(): string
    {
        return __('panel::dashboard.widget_tasks_label');
    }

    public function description(): ?string
    {
        return __('panel::dashboard.widget_tasks_description');
    }

    public function icon(): ?string
    {
        return 'check';
    }

    public function span(): WidgetSpan
    {
        return WidgetSpan::Full;
    }

    public function permission(): ?string
    {
        return 'sales:manage-orders';
    }

    public function position(): Position
    {
        return Position::priority(90);
    }

    public function visibleByDefault(): bool
    {
        return false;
    }

    /** Store-health counts, independent of the selected range. */
    public function data(DashboardRange $range): array
    {
        $unfulfilled = Order::query()
            ->whereNotNull('placed_at')
            ->whereNull('cancelled_at')
            ->open()
            ->whereState('fulfilment_status', Unfulfilled::class)
            ->count();

        $draftProducts = Product::query()
            ->where('status', Draft::$name)
            ->count();

        $outOfStock = ProductVariant::query()
            // Stock-bound variants with nothing left to sell, counting incoming
            // stock for those that sell against it.
            ->where('continue_selling', false)
            ->where(fn (Builder $query) => $query
                ->where(fn (Builder $query) => $query
                    ->where('sell_against_incoming', false)
                    ->where('stock_available', '<=', 0))
                ->orWhere(fn (Builder $query) => $query
                    ->where('sell_against_incoming', true)
                    ->whereRaw('stock_available + stock_incoming <= 0')))
            ->whereHas('product', fn (Builder $product) => $product->whereVisible())
            ->count();

        return [
            'tasks' => [
                [
                    'key' => 'unfulfilled_orders',
                    'label' => __('panel::dashboard.task_unfulfilled_orders'),
                    'count' => $unfulfilled,
                    'url' => null,
                ],
                [
                    'key' => 'draft_products',
                    'label' => __('panel::dashboard.task_draft_products'),
                    'count' => $draftProducts,
                    'url' => route('panel.products.index', ['filter' => ['status' => Draft::$name]]),
                ],
                [
                    'key' => 'out_of_stock',
                    'label' => __('panel::dashboard.task_out_of_stock'),
                    'count' => $outOfStock,
                    'url' => route('panel.products.index'),
                ],
            ],
        ];
    }
}
