<?php

namespace Lunar\Admin\Filament\Resources\ChannelResource\Pages;

use Lunar\Admin\Filament\Resources\ChannelResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateChannel extends BaseCreateRecord
{
    protected static string $resource = ChannelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
