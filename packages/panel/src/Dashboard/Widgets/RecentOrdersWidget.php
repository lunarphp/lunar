<?php

namespace Lunar\Panel\Dashboard\Widgets;

use Lunar\Core\Models\Order;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Support\Position;

class RecentOrdersWidget extends Widget
{
    public function key(): string
    {
        return 'recent-orders';
    }

    public function component(): string
    {
        return 'RecentOrdersWidget';
    }

    public function label(): string
    {
        return __('panel::dashboard.widget_recent_orders_label');
    }

    public function description(): ?string
    {
        return __('panel::dashboard.widget_recent_orders_description');
    }

    public function icon(): ?string
    {
        return 'cart';
    }

    public function permission(): ?string
    {
        return 'sales:manage-orders';
    }

    public function position(): Position
    {
        return Position::priority(30);
    }

    /** The latest activity regardless of the selected range, as a pulse check. */
    public function data(DashboardRange $range): array
    {
        $orders = Order::query()
            ->whereNotNull('placed_at')
            ->with('customer:id,first_name,last_name')
            ->latest('placed_at')
            ->limit(8)
            ->get(['id', 'customer_id', 'reference', 'placed_at', 'cancelled_at', 'closed_at', 'total', 'currency_code']);

        return [
            'orders' => $orders->map(fn (Order $order) => [
                'id' => $order->id,
                'reference' => $order->reference,
                'customer' => trim("{$order->customer?->first_name} {$order->customer?->last_name}") ?: null,
                'status' => $order->lifecycleStatus(),
                'status_label' => __('lunar::states.order.'.$order->lifecycleStatus()),
                'placed_at' => $order->placed_at?->toJSON(),
                'placed_at_human' => $order->placed_at?->diffForHumans(),
                'total' => (string) $order->format('total'),
            ])->all(),
        ];
    }
}
