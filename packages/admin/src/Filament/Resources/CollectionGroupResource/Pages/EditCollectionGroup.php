<?php

namespace Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\CollectionGroupResource;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Widgets;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditCollectionGroup extends BaseEditRecord
{
    protected static string $resource = CollectionGroupResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record, Actions\DeleteAction $action) {
                    if ($record->collections->count() > 0) {
                        Notification::make()
                            ->warning()
                            ->body(__('lunarpanel::collectiongroup.action.delete.notification.error_protected'))
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }

    protected function getDefaultFooterWidgets(): array
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
