<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Group;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListDiscounts extends BaseListRecords
{
    protected static string $resource = DiscountResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make()->schema([
                Group::make([
                    DiscountResource::getNameFormComponent(),
                    DiscountResource::getHandleFormComponent(),
                ])->columns(2),
                Group::make([
                    DiscountResource::getStartsAtFormComponent(),
                    DiscountResource::getEndsAtFormComponent(),
                ])->columns(2),
                DiscountResource::getDiscountTypeFormComponent(),
            ]),
        ];
    }
}
