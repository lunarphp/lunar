<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Filament\Support\Enums\Width;
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

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
