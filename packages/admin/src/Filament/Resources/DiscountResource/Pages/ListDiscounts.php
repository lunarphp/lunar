<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Group;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Filament\Resources\DiscountResource\Schemas\DiscountForm;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListDiscounts extends BaseListRecords
{
    protected static string $resource = DiscountResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make()->schema([
                Group::make([
                    DiscountForm::getNameComponent(),
                    DiscountForm::getHandleComponent(),
                ])->columns(2),
                Group::make([
                    DiscountForm::getStartsAtComponent(),
                    DiscountForm::getEndsAtComponent(),
                ])->columns(2),
                DiscountForm::getDiscountTypeComponent(),
            ]),
        ];
    }
}
