<?php

namespace Lunar\Admin\Filament\Resources\RegionResource\Pages;

use Filament\Actions\DeleteAction;
use Lunar\Admin\Filament\Resources\RegionResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditRegion extends BaseEditRecord
{
    protected static string $resource = RegionResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
