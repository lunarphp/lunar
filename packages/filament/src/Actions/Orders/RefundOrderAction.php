<?php

namespace Lunar\Filament\Actions\Orders;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Lunar\Core\Actions\Orders\RefundOrder;
use Lunar\Core\DataObjects\RefundRequest;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Order;
use Lunar\Filament\Actions\Concerns\ConfirmsDestructiveAction;
use Lunar\Filament\Actions\Concerns\InteractsWithTransactions;

class RefundOrderAction extends Action
{
    use ConfirmsDestructiveAction;
    use InteractsWithTransactions;

    public static function getDefaultName(): ?string
    {
        return 'refund';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.orders.refund.label'))
            ->modalHeading(__('lunar-filament::actions.orders.refund.modal_heading'))
            ->modalSubmitActionLabel(__('lunar-filament::actions.orders.refund.label'))
            ->icon('heroicon-o-backward')
            ->color('warning')
            ->schema(fn (Order $record) => $this->getRefundSchema($record))
            ->action(fn (array $data, Order $record) => $this->performRefund($data, $record))
            ->visible(fn (?Order $record = null) => $record !== null && RefundOrder::canRun($record))
            ->successNotificationTitle(__('lunar-filament::actions.orders.refund.notification.success'))
            ->failureNotificationTitle(__('lunar-filament::actions.orders.refund.notification.error'));
    }

    /**
     * @return array<int, mixed>
     */
    protected function getRefundSchema(Order $record): array
    {
        $charges = RefundOrder::charges($record);
        $availableMajor = RefundOrder::availableToRefund($record) / $record->currency->factor;

        return [
            $this->transactionSelect(
                $charges,
                fn ($charge) => "{$charge->format('amount')} - {$charge->driver} // {$charge->reference}",
            ),
            $this->amountInput($availableMajor, $record),
            $this->notesInput(),
            $this->confirmToggle(__('lunar-filament::actions.orders.refund.confirm.helper_text')),
        ];
    }

    protected function performRefund(array $data, Order $record): void
    {
        // This modal is an amount-only refund — no line picker — so the
        // whole amount rides as the manual adjustment, per RefundRequest's
        // documented amount-only fallback (empty lines).
        try {
            $result = $record->refund(new RefundRequest(
                transactionId: $data['transaction'],
                adjustment: $data['amount'],
                notes: $data['notes'] ?? null,
            ));
        } catch (OrderActionException $exception) {
            $this->failureNotification(
                fn () => Notification::make('refund_failure')->color('danger')->title($exception->getMessage())
            );

            $this->failure();
            $this->halt();

            return;
        }

        if (! $result->success) {
            $this->failureNotification(
                fn () => Notification::make('refund_failure')->color('danger')->title($result->message)
            );

            $this->failure();
            $this->halt();

            return;
        }

        $this->success();
    }
}
