<?php

namespace Lunar\Admin\Filament\Resources\LocationResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\LocationResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditLocation extends BaseEditRecord
{
    protected static string $resource = LocationResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function ($record, DeleteAction $action) {
                    if ($record->fulfilments()->exists()) {
                        Notification::make()
                            ->warning()
                            ->body(__('lunarpanel::location.action.delete.notification.error_protected'))
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
