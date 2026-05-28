<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\OrderState;

class ListOrders extends BaseListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getDefaultTabs(): array
    {
        $orderStates = app(OrderStateConfig::class)->orderStates();

        return [
            'all' => Tab::make(__('lunarpanel::order.tabs.all')),
            ...collect($orderStates)->mapWithKeys(
                /** @param class-string<OrderState> $class */
                fn (string $class) => [
                    $class::$name => Tab::make((new $class(new Order))->label())
                        ->modifyQueryUsing(fn (Builder $query) => $query->where('order_status', $class::$name)),
                ]
            ),
        ];
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
