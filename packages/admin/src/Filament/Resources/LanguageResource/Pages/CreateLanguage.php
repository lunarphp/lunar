<?php

namespace Lunar\Admin\Filament\Resources\LanguageResource\Pages;

use Lunar\Admin\Filament\Resources\LanguageResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateLanguage extends BaseCreateRecord
{
    protected static string $resource = LanguageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
