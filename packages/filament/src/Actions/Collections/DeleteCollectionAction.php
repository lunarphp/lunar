<?php

namespace Lunar\Filament\Actions\Collections;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Lunar\Core\Contracts\Actions\Collections\DeletesCollection;
use Lunar\Core\Models\Collection;

class DeleteCollectionAction extends DeleteAction
{
    public static function getDefaultName(): ?string
    {
        return 'delete_collection';
    }

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

        $this->record(fn (array $arguments) => Collection::find($arguments['id']));

        $this->action(function (Collection $record): void {
            app(DeletesCollection::class)->execute(collection: $record);

            $this->success();
        });

        $this->label(__('lunar-filament::actions.collections.delete.label'));
        $this->successNotificationTitle(__('lunar-filament::actions.collections.delete.notification.success'));
    }
}
