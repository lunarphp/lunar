<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard\Orders;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Models\Order;

class LatestOrdersTable extends TableWidget
{
    protected function getTablePollingInterval(): ?string
    {
        return '60s';
    }

    protected int|string|array $columnSpan = 'full';

    public static function getHeading(): ?string
    {
        return __('lunarpanel::widgets.dashboard.orders.latest_orders.heading');
    }

    public function table(Table $table): Table
    {
        return $table->query(function () {
            return Order::orderBy('placed_at', 'desc')->orderBy('created_at', 'desc')->limit(10);
        })->columns(
            OrderResource::getTableColumns()
        )->paginated(false)->searchable(false);
    }
}
