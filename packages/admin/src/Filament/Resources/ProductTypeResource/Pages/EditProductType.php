<?php

namespace Lunar\Admin\Filament\Resources\ProductTypeResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\ProductTypeResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProductType extends BaseEditRecord
{
    protected static string $resource = ProductTypeResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record, Actions\DeleteAction $action) {
                    if ($record->products->count() > 0) {
                        Notification::make()
                            ->warning()
                            ->body(__('lunarpanel::producttype.action.delete.notification.error_protected'))
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
