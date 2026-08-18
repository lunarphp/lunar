<?php

namespace Lunar\Admin\Filament\Resources\ProductTypeResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\ProductTypeResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\Actions\ProductTypes\DeleteProductType;

class EditProductType extends BaseEditRecord
{
    protected static string $resource = ProductTypeResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function ($record, DeleteAction $action) {
                    if (DeleteProductType::isProtected($record)) {
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
