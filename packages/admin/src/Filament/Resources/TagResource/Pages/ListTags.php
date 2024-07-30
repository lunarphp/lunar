<?php

namespace Lunar\Admin\Filament\Resources\TagResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\TagResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListTags extends BaseListRecords
{
    protected static string $resource = TagResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
