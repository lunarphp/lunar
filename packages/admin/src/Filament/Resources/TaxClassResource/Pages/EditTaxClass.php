<?php

namespace Lunar\Admin\Filament\Resources\TaxClassResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\TaxClassResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Filament\Notifications\Notification;

class EditTaxClass extends BaseEditRecord
{
    protected static string $resource = TaxClassResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record, $action) {
                    if ($record->productVariants()->exists()) {
                        Notification::make()
                            ->title(__('lunarpanel::taxclass.delete.error.title'))
                            ->body(__('lunarpanel::taxclass.delete.error.body'))
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
