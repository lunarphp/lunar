<?php

namespace Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\CollectionGroupResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\Actions\CollectionGroups\DeleteCollectionGroup;
use Lunar\Filament\Widgets\Collections\CollectionTreeView;

class EditCollectionGroup extends BaseEditRecord
{
    protected static string $resource = CollectionGroupResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function ($record, DeleteAction $action) {
                    if (DeleteCollectionGroup::isProtected($record)) {
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
            CollectionTreeView::class,
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
