<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\Cancelled;

/**
 * Move the order into the Cancelled manual-override state. Unlike OnHold,
 * Cancelled implies a final-ish disposition — automatic recomputation stays
 * paused, and downstream notifications / refund flows can key off the state.
 */
class CancelOrderAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'cancel_order';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.cancel_order.label'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(__('lunar-filament::actions.orders.cancel_order.confirm'))
            ->visible(fn (Order $record) => (string) $record->order_status !== Cancelled::$name)
            ->action(fn (Order $record) => $record->forceFill(['order_status' => Cancelled::$name])->save())
            ->successNotificationTitle(__('lunar-filament::actions.orders.cancel_order.notification.success'));
    }
}
