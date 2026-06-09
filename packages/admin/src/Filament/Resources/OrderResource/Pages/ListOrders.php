<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

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
        return [
            'open' => Tab::make(__('lunarpanel::order.tabs.open'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('closed_at')),
            'closed' => Tab::make(__('lunarpanel::order.tabs.closed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('closed_at')),
            'all' => Tab::make(__('lunarpanel::order.tabs.all')),
        ];
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
