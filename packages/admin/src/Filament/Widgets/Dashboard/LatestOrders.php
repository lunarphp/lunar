<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Models\Order;

class LatestOrders extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public static function getHeading(): ?string
    {
        return __('lunarpanel::widgets.dashboard.latest_orders.heading');
    }

    public function table(Table $table): Table
    {
        return OrderResource::table(
            $table->query(function () {
                return Order::orderBy('placed_at', 'desc')->orderBy('created_at', 'desc')->limit(10);
            })
        )->heading(
            $this->getHeading()
        )->searchable(false)->paginated(false);
    }
}
