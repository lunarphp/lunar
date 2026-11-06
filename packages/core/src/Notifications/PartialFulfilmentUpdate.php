<?php

namespace Lunar\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Contracts\Notifications\AcceptsCustomerMessage;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Facades\FulfilmentMethods;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\Order;

/**
 * A manual-only "part of your order is still to come" email. Lists the lines
 * still outstanding — ordered quantity minus the quantity on fulfilments that
 * have gone to the customer (the Fulfilled and Returned categories, as the
 * order rollup counts them) — so a delay update is a pick-and-send rather than
 * free text. Registered as the `partial-fulfilment-update` default.
 */
class PartialFulfilmentUpdate extends Notification implements AcceptsCustomerMessage
{
    use Queueable;

    public function __construct(
        public Order $order,
        public ?string $message = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing(['billingAddress', 'shippingAddress']);

        $reference = $this->order->reference ?? (string) $this->order->id;

        return (new MailMessage)
            ->subject(__('lunar::notifications.partial_fulfilment_update.subject', ['reference' => $reference]))
            ->markdown('lunar::mail.orders.partial-fulfilment-update', [
                'order' => $this->order,
                'reference' => $reference,
                'lines' => $this->outstandingLineRows(),
                'message' => $this->message,
            ]);
    }

    /**
     * @return array<int, array{description: string, option: ?string, quantity: int}>
     */
    protected function outstandingLineRows(): array
    {
        $this->order->loadMissing('fulfillableLines');

        $dispatchedStates = array_merge(
            FulfilmentMethods::stateNamesIn(FulfilmentStateCategory::Fulfilled),
            FulfilmentMethods::stateNamesIn(FulfilmentStateCategory::Returned),
        );

        $dispatchedByLine = FulfilmentLine::query()
            ->whereIn('order_line_id', $this->order->fulfillableLines->pluck('id'))
            ->whereHas('fulfilment', fn ($query) => $query->whereIn('state', $dispatchedStates))
            ->selectRaw('order_line_id, sum(quantity) as quantity')
            ->groupBy('order_line_id')
            ->pluck('quantity', 'order_line_id');

        return $this->order->fulfillableLines
            ->map(fn ($line) => [
                'description' => (string) $line->description,
                'option' => $line->option,
                'quantity' => (int) $line->quantity - (int) ($dispatchedByLine[$line->id] ?? 0),
            ])
            ->filter(fn (array $row) => $row['quantity'] > 0)
            ->values()
            ->all();
    }
}
