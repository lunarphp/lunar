<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\Cancelled;
use Lunar\Core\States\Order\Order\OnHold;

/**
 * Park an order in the OnHold state. Use Resume to move it back into the
 * active lifecycle.
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
            ->visible(fn (Order $record) => ! in_array((string) $record->status, [OnHold::$name, Cancelled::$name], true))
            ->action(fn (Order $record) => $record->forceFill(['status' => OnHold::$name])->save())
            ->successNotificationTitle(__('lunar-filament::actions.orders.place_on_hold.notification.success'));
    }
}
