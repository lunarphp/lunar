<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Lunar\Core\Models\Order;

/**
 * Leave the OnHold / Cancelled manual-override state and let the order's
 * payment + fulfilment columns drive order_status again. We trigger
 * recomputation directly rather than picking a target — the resolver
 * already knows where to land.
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
            ->visible(fn (Order $record) => $record->order_status->isManualOverride())
            ->action(function (Order $record) {
                // Park briefly in a non-override state so the observer fires
                // computeOrderStatus() and writes the real computed value.
                $record->forceFill(['order_status' => 'awaiting-payment'])->save();
            })
            ->successNotificationTitle(__('lunar-filament::actions.orders.resume_order.notification.success'));
    }
}
