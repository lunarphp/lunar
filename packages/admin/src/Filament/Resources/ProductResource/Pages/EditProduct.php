<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProduct extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Basic Information';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::basic-information');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
