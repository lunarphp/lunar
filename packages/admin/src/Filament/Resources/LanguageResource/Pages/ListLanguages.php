<?php

namespace Lunar\Admin\Filament\Resources\LanguageResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\LanguageResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListLanguages extends BaseListRecords
{
    protected static string $resource = LanguageResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
