<?php

namespace Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Lunar\Admin\Filament\Resources\AttributeGroupResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditAttributeGroup extends BaseEditRecord
{
    protected static string $resource = AttributeGroupResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function ($record, DeleteAction $action) {
                    if ($record->attributes->count() > 0) {
                        Notification::make()
                            ->warning()
                            ->body(__('lunarpanel::attributegroup.action.delete.notification.error_protected'))
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }
}
