<?php

namespace Lunar\Admin\Filament\Resources\PromotionResource\Pages;

use Lunar\Admin\Filament\Resources\PromotionResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreatePromotion extends BaseCreateRecord
{
    protected static string $resource = PromotionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
