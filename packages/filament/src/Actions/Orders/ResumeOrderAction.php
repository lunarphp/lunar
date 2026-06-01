<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\OnHold;

/**
 * Resume an order that is OnHold, moving it back into the active lifecycle
 * at AwaitingPayment.
 */
class ResumeOrderAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'resume_order';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.resume_order.label'))
            ->icon('heroicon-o-play-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription(__('lunar-filament::actions.orders.resume_order.confirm'))
            ->visible(fn (Order $record) => (string) $record->status === OnHold::$name)
            ->action(function (Order $record) {
                $record->forceFill(['status' => 'awaiting-payment'])->save();
            })
            ->successNotificationTitle(__('lunar-filament::actions.orders.resume_order.notification.success'));
    }
}
