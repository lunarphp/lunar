<?php

namespace Lunar\Filament\Actions\Attributes;

use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Attributes\DeletesAttribute;

/**
 * Bulk-delete attributes through the core DeletesAttribute action (spec
 * 0063). System attributes in the selection are skipped with a warning
 * rather than failing the whole batch.
 */
class DeleteAttributesBulkAction extends DeleteBulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function (Collection $records): void {
            [$protected, $deletable] = $records->partition(fn ($record) => (bool) $record->system);

            $deletable->each(fn ($record) => app(DeletesAttribute::class)->execute($record));

            if ($protected->isNotEmpty()) {
                Notification::make()
                    ->warning()
                    ->body(__('lunar-filament::attribute.actions.delete.notification.error_protected'))
                    ->send();
            }

            if ($deletable->isNotEmpty()) {
                $this->success();
            }
        });
    }
}
