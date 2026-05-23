<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Lunar\Core\Actions\Orders\MarkOrderAsShipped;
use Lunar\Core\Models\Order;

class MarkOrderAsShippedAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'mark_as_shipped';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.mark_as_shipped.label'))
            ->icon('heroicon-o-truck')
            ->color('success')
            ->action(fn (Order $record) => MarkOrderAsShipped::run($record))
            ->successNotificationTitle(__('lunar-filament::actions.orders.mark_as_shipped.notification.success'));
    }
}
