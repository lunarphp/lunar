<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditDiscount extends BaseEditRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
