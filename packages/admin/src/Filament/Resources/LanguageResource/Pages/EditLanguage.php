<?php

namespace Lunar\Admin\Filament\Resources\LanguageResource\Pages;

use Filament\Actions\DeleteAction;
use Lunar\Admin\Filament\Resources\LanguageResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditLanguage extends BaseEditRecord
{
    protected static string $resource = LanguageResource::class;

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
