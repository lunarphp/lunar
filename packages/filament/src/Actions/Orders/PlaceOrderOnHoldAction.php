<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\OnHold;

/**
 * Park an order in the OnHold manual-override state. While the order_status
 * is OnHold, payment / fulfilment changes no longer recompute order_status.
 * Use Resume (or any direct order_status edit out of OnHold) to leave.
 */
class PlaceOrderOnHoldAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'place_on_hold';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.place_on_hold.label'))
            ->icon('heroicon-o-pause-circle')
            ->color('warning')
            ->requiresConfirmation()
            ->modalDescription(__('lunar-filament::actions.orders.place_on_hold.confirm'))
            ->visible(fn (Order $record) => ! $record->order_status->isManualOverride())
            ->action(fn (Order $record) => $record->forceFill(['order_status' => OnHold::$name])->save())
            ->successNotificationTitle(__('lunar-filament::actions.orders.place_on_hold.notification.success'));
    }
}
