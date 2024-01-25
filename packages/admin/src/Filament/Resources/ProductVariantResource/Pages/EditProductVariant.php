<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProductVariant extends BaseEditRecord
{
    protected static string $resource = ProductVariantResource::class;

    protected static ?string $title = 'Basic Information';

    public static bool $formActionsAreSticky = true;

    protected function getDefaultHeaderActions(): array
    {
        return [];
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
