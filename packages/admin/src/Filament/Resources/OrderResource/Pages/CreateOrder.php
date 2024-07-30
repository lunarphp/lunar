<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateOrder extends BaseCreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
