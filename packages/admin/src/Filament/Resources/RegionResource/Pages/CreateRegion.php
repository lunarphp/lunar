<?php

namespace Lunar\Admin\Filament\Resources\RegionResource\Pages;

use Lunar\Admin\Filament\Resources\RegionResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateRegion extends BaseCreateRecord
{
    protected static string $resource = RegionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
