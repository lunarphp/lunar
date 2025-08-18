<?php

namespace Lunar\Admin\Support\Actions\Collections;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Lunar\Models\Collection;

class DeleteCollection extends DeleteAction
{
    public function setUp(): void
    {
        parent::setUp();

        $this->before(function ($record, $action) {
            if ($record->children()->exists()) {
                Notification::make()
                    ->title(__('lunarpanel::actions.collections.delete.notifications.cannot_delete.title'))
                    ->body(__('lunarpanel::actions.collections.delete.notifications.cannot_delete.body'))
                    ->danger()
                    ->send();

                $action->halt();
            }
        });

        $this->record(function (array $arguments) {
            return Collection::find($arguments['id']);
        });

        $this->label(
            __('lunarpanel::actions.collections.delete.label')
        );
    }
}
