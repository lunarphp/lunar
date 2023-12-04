<?php

namespace Lunar\Admin\Filament\Resources\ChannelResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\ChannelResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditChannel extends BaseEditRecord
{
    protected static string $resource = ChannelResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
