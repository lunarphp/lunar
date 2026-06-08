<?php

namespace Lunar\Admin\Filament\Resources\LocationResource\Pages;

use Lunar\Admin\Filament\Resources\LocationResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateLocation extends BaseCreateRecord
{
    protected static string $resource = LocationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
