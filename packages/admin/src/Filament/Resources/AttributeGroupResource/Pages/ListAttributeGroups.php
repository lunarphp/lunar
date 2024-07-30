<?php

namespace Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\AttributeGroupResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListAttributeGroups extends BaseListRecords
{
    protected static string $resource = AttributeGroupResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
