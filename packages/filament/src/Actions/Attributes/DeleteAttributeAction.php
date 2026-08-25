<?php

namespace Lunar\Filament\Actions\Attributes;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Attributes\DeletesAttribute;

/**
 * Delete an attribute through the core DeletesAttribute action (spec 0063).
 * System attributes are protected — the panel and the Filament admin share
 * the rule through the core action.
 */
class DeleteAttributeAction extends DeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->before(function (Model $record, DeleteAttributeAction $action) {
            if ($record->system) {
                Notification::make()
                    ->warning()
                    ->body(__('lunar-filament::attribute.actions.delete.notification.error_protected'))
                    ->send();

                $action->cancel();
            }
        });

        $this->action(function (Model $record): void {
            app(DeletesAttribute::class)->execute($record);

            $this->success();
        });
    }
}
