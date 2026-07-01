<?php

namespace Lunar\Admin\Filament\Resources\PromotionResource\Pages;

use Filament\Actions\CreateAction;
use Lunar\Admin\Filament\Resources\PromotionResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListPromotions extends BaseListRecords
{
    protected static string $resource = PromotionResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
