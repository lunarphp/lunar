<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Lunar\Core\Actions\Orders\CancelOrder;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Facades\CancelReasons;
use Lunar\Core\Facades\OrderNotifications;
use Lunar\Core\Models\Order;

/**
 * Cancel an unfulfilled order. Covers the status side only — no refund is
 * issued and no stock is restocked (future specs). The notification toggle
 * gates the customer email via the OrderCancelled event.
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
            ->modalHeading(__('lunar-filament::actions.orders.cancel_order.modal_heading'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->schema([
                Select::make('reason')
                    ->label(__('lunar-filament::actions.orders.cancel_order.reason'))
                    ->options(CancelReasons::all())
                    ->native(false),
                Textarea::make('note')
                    ->label(__('lunar-filament::actions.orders.cancel_order.note'))
                    ->helperText(__('lunar-filament::actions.orders.cancel_order.note_help')),
                Toggle::make('notify')
                    ->label(__('lunar-filament::actions.orders.cancel_order.notify'))
                    ->default(true)
                    // Only meaningful when a cancellation notification is wired
                    // up; its presence is the cue the action will email the
                    // customer.
                    ->visible(fn (): bool => OrderNotifications::triggeredBy('cancelled', NotificationScope::Order) !== []),
            ])
            ->action(fn (Order $record, array $data) => $record->cancel(
                $data['reason'] ?? null,
                $data['note'] ?? null,
                (bool) ($data['notify'] ?? true),
            ))
            ->visible(fn (?Order $record = null) => $record !== null && CancelOrder::canRun($record))
            ->successNotificationTitle(__('lunar-filament::actions.orders.cancel_order.notification.success'));
    }
}
