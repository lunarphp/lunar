<?php

namespace Lunar\Admin\Filament\Resources\ChannelResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\ChannelResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListChannels extends BaseListRecords
{
    protected static string $resource = ChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
