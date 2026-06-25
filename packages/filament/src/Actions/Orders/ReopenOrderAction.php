<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Lunar\Core\Actions\Orders\ReopenOrder;
use Lunar\Core\Models\Order;

class ReopenOrderAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reopen_order';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.reopen.label'))
            ->icon('heroicon-o-archive-box-arrow-down')
            ->color('gray')
            ->requiresConfirmation()
            ->action(fn (Order $record) => $record->reopen())
            ->visible(fn (?Order $record = null) => $record !== null && ReopenOrder::canRun($record))
            ->successNotificationTitle(__('lunar-filament::actions.orders.reopen.notification.success'));
    }
}
