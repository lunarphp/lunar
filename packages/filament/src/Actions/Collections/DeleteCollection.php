<?php

namespace Lunar\Filament\Actions\Collections;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Lunar\Core\Models\Collection;

class DeleteCollection extends DeleteAction
{
    public function setUp(): void
    {
        parent::setUp();

        $this->before(function ($record, $action) {
            if ($record->children()->exists()) {
                Notification::make()
                    ->title(__('lunar-filament::actions.collections.delete.notifications.cannot_delete.title'))
                    ->body(__('lunar-filament::actions.collections.delete.notifications.cannot_delete.body'))
                    ->danger()
                    ->send();

                $action->halt();
            }
        });

        $this->record(function (array $arguments) {
            return Collection::find($arguments['id']);
        });

        $this->label(
            __('lunar-filament::actions.collections.delete.label')
        );
    }
}
