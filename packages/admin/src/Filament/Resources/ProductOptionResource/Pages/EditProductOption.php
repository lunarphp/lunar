<?php

namespace Lunar\Admin\Filament\Resources\ProductOptionResource\Pages;

use Filament\Actions\DeleteAction;
use Lunar\Admin\Filament\Resources\ProductOptionResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Filament\RelationManagers\ProductOption\ValuesRelationManager;

class EditProductOption extends BaseEditRecord
{
    protected static string $resource = ProductOptionResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return $this->record->shared ? [
            ValuesRelationManager::class,
        ] : [];
    }
}
