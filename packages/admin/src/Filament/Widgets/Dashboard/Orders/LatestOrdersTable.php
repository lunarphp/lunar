<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard\Orders;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Lunar\Admin\Filament\Resources\OrderResource\Tables\OrderTable;
use Lunar\Core\Models\Order;

class LatestOrdersTable extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table->query(function () {
            return Order::with(['currency'])->orderBy('placed_at', 'desc')->orderBy('created_at', 'desc')->limit(10);
        })->columns(
            OrderTable::getColumns()
        )->paginated(false)->searchable(false)
            ->heading(__('lunarpanel::widgets.dashboard.orders.latest_orders.heading'))
            ->poll('60s');
    }
}
