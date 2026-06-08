<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Lunar\Core\Actions\Orders\MarkOrderAsComplete;
use Lunar\Core\Contracts\Actions\Orders\MarksOrderAsComplete;
use Lunar\Core\Models\Order;

class MarkOrderAsCompleteAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'mark_as_complete';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.mark_as_complete.label'))
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->action(fn (Order $record) => app(MarksOrderAsComplete::class)->execute($record))
            ->visible(fn (?Order $record = null) => $record !== null && MarkOrderAsComplete::canRun($record))
            ->successNotificationTitle(__('lunar-filament::actions.orders.mark_as_complete.notification.success'));
    }
}
