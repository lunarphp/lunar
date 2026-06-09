<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Lunar\Core\Actions\Orders\CloseOrder;
use Lunar\Core\Contracts\Actions\Orders\ClosesOrder;
use Lunar\Core\Models\Order;

class CloseOrderAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'close_order';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.close.label'))
            ->icon('heroicon-o-archive-box')
            ->color('gray')
            ->requiresConfirmation()
            ->action(fn (Order $record) => app(ClosesOrder::class)->execute($record))
            ->visible(fn (?Order $record = null) => $record !== null && CloseOrder::canRun($record))
            ->successNotificationTitle(__('lunar-filament::actions.orders.close.notification.success'));
    }
}
