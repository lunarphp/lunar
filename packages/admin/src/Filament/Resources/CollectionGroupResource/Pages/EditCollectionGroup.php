<?php

namespace Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Lunar\Admin\Filament\Resources\CollectionGroupResource;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Widgets;

class EditCollectionGroup extends EditRecord
{
    protected static string $resource = CollectionGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            Widgets\CollectionTreeView::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
