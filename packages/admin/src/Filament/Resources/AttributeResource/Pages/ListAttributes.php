<?php

namespace Lunar\Admin\Filament\Resources\AttributeResource\Pages;

use Filament\Actions\CreateAction;
use Lunar\Admin\Filament\Resources\AttributeResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListAttributes extends BaseListRecords
{
    protected static string $resource = AttributeResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
