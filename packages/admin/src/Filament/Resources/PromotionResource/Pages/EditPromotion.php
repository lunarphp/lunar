<?php

namespace Lunar\Admin\Filament\Resources\PromotionResource\Pages;

use Filament\Actions\DeleteAction;
use Lunar\Admin\Filament\Resources\PromotionResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditPromotion extends BaseEditRecord
{
    protected static string $resource = PromotionResource::class;

    public function getTitle(): string
    {
        return __('lunarpanel::promotion.pages.edit.title');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
