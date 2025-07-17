<?php

namespace Lunar\Admin\Filament\Resources\CustomerGroupResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\CustomerGroupResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditCustomerGroup extends BaseEditRecord
{
    protected static string $resource = CustomerGroupResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function ($record, DeleteAction $action) {
                    if ($record->customers->count() > 0) {
                        Notification::make()
                            ->warning()
                            ->body(__('lunarpanel::customergroup.action.delete.notification.error_protected'))
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
