<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Lunar\Core\Facades\OrderNotifications;
use Lunar\Core\Models\Order;

/**
 * Compose and send a customer notification on demand: pick a variant from the
 * order-scoped, manually-sendable entries in the OrderNotifications catalogue,
 * optionally attach a one-off message, and choose recipients (the order
 * contacts plus an ad-hoc address). Delegates the send and the timeline log to
 * the core NotifyCustomer action. The same catalogue feeds the automatic sends,
 * so a notification that fires on a lifecycle event can also be resent here.
 *
 * Hidden while no order-scoped notification is manually sendable.
 */
class NotifyCustomerAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'notify_customer';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.notify_customer.label'))
            ->modalHeading(__('lunar-filament::actions.orders.notify_customer.modal_heading'))
            ->icon('heroicon-o-envelope')
            ->schema([
                Select::make('notification')
                    ->label(__('lunar-filament::actions.orders.notify_customer.variant'))
                    ->options(OrderNotifications::sendable())
                    ->required()
                    ->native(false),
                Textarea::make('message')
                    ->label(__('lunar-filament::actions.orders.notify_customer.message'))
                    ->helperText(__('lunar-filament::actions.orders.notify_customer.message_help')),
                CheckboxList::make('recipients')
                    ->label(__('lunar-filament::actions.orders.notify_customer.recipients'))
                    ->options(fn (Order $record): array => static::contactOptions($record))
                    ->default(fn (Order $record): array => array_keys(static::contactOptions($record))),
                TextInput::make('additional_email')
                    ->label(__('lunar-filament::actions.orders.notify_customer.additional_email'))
                    ->email(),
            ])
            ->action(function (Order $record, array $data): void {
                $recipients = array_values(array_filter(array_merge(
                    $data['recipients'] ?? [],
                    array_filter([$data['additional_email'] ?? null]),
                )));

                $record->notifyCustomer(
                    $data['notification'],
                    $data['message'] ?? null,
                    $recipients,
                );
            })
            ->visible(fn (?Order $record = null): bool => $record !== null && OrderNotifications::sendable() !== [])
            ->successNotificationTitle(__('lunar-filament::actions.orders.notify_customer.notification.success'));
    }

    /**
     * The order's contact emails as checkbox options, keyed by email so a
     * shared billing/shipping address collapses to one entry.
     *
     * @return array<string, string>
     */
    protected static function contactOptions(Order $record): array
    {
        return collect([
            'billing' => $record->billingAddress?->contact_email,
            'shipping' => $record->shippingAddress?->contact_email,
        ])
            ->filter()
            ->mapWithKeys(fn (string $email, string $type): array => [
                $email => __("lunar-filament::actions.orders.notify_customer.recipients_{$type}", ['email' => $email]),
            ])
            ->all();
    }
}
