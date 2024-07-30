<?php

namespace Lunar\Admin\Filament\Resources\TagResource\Pages;

use Lunar\Admin\Filament\Resources\TagResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateTag extends BaseCreateRecord
{
    protected static string $resource = TagResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
