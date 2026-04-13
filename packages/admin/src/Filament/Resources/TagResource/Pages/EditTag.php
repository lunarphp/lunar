<?php

namespace Lunar\Admin\Filament\Resources\TagResource\Pages;

use Filament\Actions\DeleteAction;
use Lunar\Admin\Filament\Resources\TagResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditTag extends BaseEditRecord
{
    protected static string $resource = TagResource::class;

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
